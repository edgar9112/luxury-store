<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function index(Request $request): View
    {
        $cart = $this->carts->find($request)?->load(['items.variant.product']);

        $totals = $cart
            ? $this->carts->totals($cart)
            : ['subtotal' => 0, 'shipping' => 0, 'discount' => 0, 'total' => 0, 'item_count' => 0];

        return view('bolsa', [
            'items' => $cart?->items ?? collect(),
            'subtotal' => $totals['subtotal'],
            'shipping' => $totals['shipping'],
            'total' => $totals['total'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $variant = ProductVariant::query()->with('product')->findOrFail($data['variant_id']);

        abort_unless($variant->is_active && $variant->product?->is_active, 404);

        if (! $variant->isInStock()) {
            return back()->withErrors([
                'quantity' => 'Esta variante está agotada. Elige otra talla o acabado.',
            ]);
        }

        $cart = $this->carts->resolve($request);

        $item = $cart->items()->firstOrNew([
            'product_variant_id' => $variant->id,
        ]);

        $quantity = (int) ($item->quantity ?? 0) + (int) $data['quantity'];
        $item->quantity = min($quantity, $variant->stock);
        $item->save();

        return redirect()
            ->route('product.show', $variant->product)
            ->with('cart_status', '«'.$variant->product->name.'» se añadió a la bolsa.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $this->ensureItemBelongsToCart($request, $item);

        $variant = $item->variant()->with('product')->first();

        abort_unless($variant && $variant->is_active && $variant->product?->is_active, 404);

        if (! $variant->isInStock()) {
            $item->delete();

            return redirect()
                ->route('cart.index')
                ->with('cart_status', 'Esta pieza se agotó y se retiró de la bolsa.');
        }

        $item->quantity = min((int) $data['quantity'], $variant->stock);
        $item->save();

        return redirect()
            ->route('cart.index')
            ->with('cart_status', 'Cantidad actualizada.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToCart($request, $item);

        $item->delete();

        return redirect()
            ->route('cart.index')
            ->with('cart_status', 'Pieza retirada de la bolsa.');
    }

    private function ensureItemBelongsToCart(Request $request, CartItem $item): void
    {
        $cart = $this->carts->find($request);

        abort_if($cart === null || (int) $item->cart_id !== (int) $cart->id, 404);
    }
}
