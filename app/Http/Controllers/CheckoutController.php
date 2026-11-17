<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\OrderPayment;
use App\Services\StripeCheckout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly StripeCheckout $stripe,
        private readonly OrderPayment $payments
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->carts->find($request)?->load(['items.variant.product']);

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('cart_status', 'Tu bolsa está vacía. Añade una pieza antes de finalizar la compra.');
        }

        return view('checkout', [
            'items' => $cart->items,
            'totals' => $this->carts->totals($cart),
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'shipping_line1' => ['required', 'string', 'max:150'],
            'shipping_line2' => ['nullable', 'string', 'max:150'],
            'shipping_city' => ['required', 'string', 'max:80'],
            'shipping_province' => ['nullable', 'string', 'max:80'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'size:2'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $cart = $this->carts->find($request)?->load(['items.variant.product']);

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('cart_status', 'Tu bolsa está vacía. Añade una pieza antes de finalizar la compra.');
        }

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            if (! $variant || ! $variant->is_active || ! $variant->product?->is_active
                || ! $variant->isInStock() || $variant->stock < $item->quantity) {
                return redirect()
                    ->route('cart.index')
                    ->withErrors(['quantity' => 'Una de las piezas de tu bolsa ya no está disponible. Revísala e inténtalo de nuevo.']);
            }
        }

        $totals = $this->carts->totals($cart);
        $coupon = null;

        if (filled($data['coupon_code'] ?? null)) {
            $coupon = Coupon::query()
                ->where('code', strtoupper(trim((string) $data['coupon_code'])))
                ->first();

            if (! $coupon?->isValidFor($totals['subtotal'])) {
                return back()
                    ->withInput()
                    ->withErrors(['coupon_code' => 'El cupón no es válido o no cumple los requisitos de este pedido.']);
            }

            $totals = $this->carts->totals($cart, $coupon);
        }

        if (! $this->stripe->configured() && ! app()->environment('local', 'testing')) {
            abort(503, 'Stripe no está configurado. Define STRIPE_SECRET para poder cobrar pedidos.');
        }

        $order = DB::transaction(function () use ($cart, $data, $totals, $coupon, $request) {
            foreach ($cart->items as $item) {
                $variant = ProductVariant::query()
                    ->whereKey($item->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock < $item->quantity) {
                    throw new RuntimeException('Stock insuficiente para '.$variant->sku);
                }

                $variant->decrement('stock', $item->quantity);
            }

            $order = Order::create([
                'number' => $this->orderNumber(),
                'user_id' => $request->user()?->id,
                'coupon_id' => $coupon?->id,
                'cart_id' => $cart->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'payment_method' => $this->stripe->configured() ? 'stripe' : 'demo',
                'currency' => (string) config('store.currency', 'USD'),
                'subtotal_cents' => $totals['subtotal'],
                'shipping_cents' => $totals['shipping'],
                'discount_cents' => $totals['discount'],
                'total_cents' => $totals['total'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'shipping_line1' => $data['shipping_line1'],
                'shipping_line2' => $data['shipping_line2'],
                'shipping_city' => $data['shipping_city'],
                'shipping_province' => $data['shipping_province'],
                'shipping_postal_code' => $data['shipping_postal_code'],
                'shipping_country' => strtoupper((string) $data['shipping_country']),
                'notes' => $data['notes'],
            ]);

            foreach ($cart->items as $item) {
                $variant = $item->variant;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name(),
                    'sku' => $variant->sku,
                    'unit_price_cents' => $variant->effectivePriceCents(),
                    'quantity' => $item->quantity,
                    'total_cents' => $variant->effectivePriceCents() * $item->quantity,
                ]);
            }

            $coupon?->increment('used_count');

            return $order;
        });

        session(['last_order_number' => $order->number]);

        if (! $this->stripe->configured()) {
            $this->payments->markPaid($order, null, null, 'demo');

            return redirect()
                ->route('order.show', $order)
                ->with('order_status', 'Pedido confirmado en modo demostración. No se ha realizado ningún cobro.');
        }

        try {
            $session = $this->stripe->createSession(
                $order,
                route('checkout.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
                route('checkout.cancel', $order)
            );
        } catch (RuntimeException $exception) {
            report($exception);
            $this->payments->markCancelled($order);

            return back()
                ->withInput()
                ->withErrors(['payment' => 'No se pudo iniciar el pago con Stripe. Inténtalo de nuevo.']);
        }

        $order->forceFill(['stripe_session_id' => $session['id']])->save();

        return redirect()->away($session['url']);
    }

    public function success(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $sessionId = $request->string('session_id')->trim()->toString();

        if ($order->payment_status !== PaymentStatus::Paid && $sessionId !== '' && $this->stripe->configured()) {
            $session = $this->stripe->retrieveSession($sessionId);
            $reference = is_string($session['client_reference_id'] ?? null) ? $session['client_reference_id'] : null;

            if ($session && $reference === $order->number && ($session['payment_status'] ?? null) === 'paid') {
                $intent = is_string($session['payment_intent'] ?? null) ? $session['payment_intent'] : null;
                $this->payments->markPaid($order, $session['id'] ?? null, $intent);
            }
        }

        return redirect()->route('order.show', $order);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $this->payments->markCancelled($order);

        return redirect()
            ->route('order.show', $order)
            ->with('order_status', 'Pago cancelado. Tu bolsa sigue disponible para intentarlo de nuevo.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        if ($order->user_id !== null) {
            abort_unless($request->user()?->id === $order->user_id, 404);

            return;
        }

        abort_unless(session('last_order_number') === $order->number, 404);
    }

    private function orderNumber(): string
    {
        do {
            $number = 'LS-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
