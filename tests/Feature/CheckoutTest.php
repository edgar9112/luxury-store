<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private ?string $stripeReference = null;

    /**
     * @return array{0: Product, 1: ProductVariant}
     */
    private function createProduct(int $priceCents = 100000, int $stock = 5): array
    {
        $category = Category::create([
            'name' => 'Relojes',
            'slug' => 'relojes-'.Str::lower(Str::random(6)),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Reloj de Prueba',
            'slug' => 'reloj-de-prueba-'.Str::lower(Str::random(6)),
            'sku' => 'TEST-'.Str::upper(Str::random(8)),
            'short_description' => 'Pieza de prueba con materiales nobles.',
            'description' => 'Descripción completa de la pieza de prueba.',
            'price_cents' => $priceCents,
            'material' => 'Oro',
            'collection' => 'Pruebas',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-'.Str::upper(Str::random(8)),
            'size' => '40 mm',
            'color' => 'Oro',
            'color_hex' => '#c9a961',
            'stock' => $stock,
            'is_active' => true,
        ]);

        return [$product, $variant];
    }

    private function cartFor(User $user, ProductVariant $variant, int $quantity = 2): Cart
    {
        $cart = Cart::create(['user_id' => $user->id]);

        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    /**
     * @return array<string, string>
     */
    private function shippingData(): array
    {
        return [
            'customer_name' => 'María García',
            'customer_email' => 'maria@example.com',
            'customer_phone' => '612345678',
            'shipping_line1' => 'Calle de Prueba 12',
            'shipping_line2' => null,
            'shipping_city' => 'Nueva York',
            'shipping_province' => 'NY',
            'shipping_postal_code' => '10001',
            'shipping_country' => 'US',
            'coupon_code' => null,
            'notes' => null,
        ];
    }

    private function fakeStripe(): void
    {
        config()->set('stripe.secret', 'sk_test_123');

        Http::fake([
            'api.stripe.com/v1/checkout/sessions/*' => fn () => Http::response([
                'id' => 'cs_test_123',
                'client_reference_id' => $this->stripeReference ?? 'LS-TEST',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_test_123',
            ]),
            'api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/pay/cs_test_123',
            ]),
        ]);
    }

    public function test_checkout_requires_a_non_empty_cart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_checkout_page_shows_the_cart_and_totals(): void
    {
        [$product] = $this->createProduct();
        $user = User::factory()->create();
        $this->cartFor($user, $product->variants()->first(), 2);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Finalizar compra')
            ->assertSee('$2,000.00')
            ->assertSee('Gratis');
    }

    public function test_demo_checkout_creates_a_paid_order_and_clears_the_cart(): void
    {
        config()->set('stripe.secret', null);

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 2);

        $response = $this->actingAs($user)
            ->post(route('checkout.store'), $this->shippingData());

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('order.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'status' => OrderStatus::Paid->value,
            'payment_status' => PaymentStatus::Paid->value,
            'payment_method' => 'demo',
            'subtotal_cents' => 200000,
            'total_cents' => 200000,
            'customer_email' => 'maria@example.com',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'total_cents' => 200000,
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
        $this->assertSame(3, $variant->fresh()->stock);

        $this->actingAs($user)
            ->get(route('order.show', $order))
            ->assertOk()
            ->assertSee('Gracias por tu compra')
            ->assertSee('Modo demostración');
    }

    public function test_coupon_discount_is_applied_to_the_order(): void
    {
        config()->set('stripe.secret', null);

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $this->cartFor($user, $variant, 2);

        Coupon::create([
            'code' => 'BIENVENIDA10',
            'type' => CouponType::Percentage,
            'value' => 10,
            'min_order_cents' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('checkout.store'), array_merge($this->shippingData(), ['coupon_code' => 'bienvenida10']))
            ->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'discount_cents' => 20000,
            'total_cents' => 180000,
        ]);

        $this->assertSame(1, Coupon::query()->firstOrFail()->used_count);
    }

    public function test_invalid_coupon_is_rejected_without_creating_an_order(): void
    {
        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $this->cartFor($user, $variant, 2);

        $this->actingAs($user)
            ->post(route('checkout.store'), array_merge($this->shippingData(), ['coupon_code' => 'NOEXISTE']))
            ->assertSessionHasErrors('coupon_code');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_stripe_checkout_creates_a_pending_order_and_redirects_to_stripe(): void
    {
        $this->fakeStripe();

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 2);

        $this->actingAs($user)
            ->post(route('checkout.store'), $this->shippingData())
            ->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

        $order = Order::query()->firstOrFail();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Pending->value,
            'payment_status' => PaymentStatus::Pending->value,
            'payment_method' => 'stripe',
            'stripe_session_id' => 'cs_test_123',
        ]);

        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
        $this->assertSame(3, $variant->fresh()->stock);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/checkout/sessions')
            && str_contains((string) $request->body(), 'cs_test_123') === false);
    }

    public function test_success_page_confirms_a_paid_stripe_session(): void
    {
        $this->fakeStripe();

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 2);

        $this->actingAs($user)->post(route('checkout.store'), $this->shippingData());

        $order = Order::query()->firstOrFail();
        $this->stripeReference = $order->number;

        $this->actingAs($user)
            ->get(route('checkout.success', ['order' => $order, 'session_id' => 'cs_test_123']))
            ->assertRedirect(route('order.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Paid->value,
            'payment_status' => PaymentStatus::Paid->value,
            'stripe_payment_intent' => 'pi_test_123',
        ]);

        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_cancelling_the_payment_restores_stock_and_keeps_the_cart(): void
    {
        $this->fakeStripe();

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 2);

        $this->actingAs($user)->post(route('checkout.store'), $this->shippingData());

        $order = Order::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('checkout.cancel', $order))
            ->assertRedirect(route('order.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Cancelled->value,
            'payment_status' => PaymentStatus::Failed->value,
        ]);

        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
        $this->assertSame(5, $variant->fresh()->stock);
    }

    public function test_webhook_with_a_valid_signature_marks_the_order_as_paid(): void
    {
        $this->fakeStripe();
        config()->set('stripe.webhook_secret', 'whsec_test');

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 2);

        $this->actingAs($user)->post(route('checkout.store'), $this->shippingData());
        $order = Order::query()->firstOrFail();

        $payload = [
            'id' => 'evt_test',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_123',
                'client_reference_id' => $order->number,
                'payment_status' => 'paid',
                'payment_intent' => 'pi_webhook_123',
            ]],
        ];

        $timestamp = time();
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $this->withHeader('Stripe-Signature', "t={$timestamp},v1={$signature}")
            ->call('POST', route('stripe.webhook'), [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            ], $body)
            ->assertOk()
            ->assertJson(['received' => true]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Paid->value,
            'payment_status' => PaymentStatus::Paid->value,
            'stripe_payment_intent' => 'pi_webhook_123',
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $this->fakeStripe();
        config()->set('stripe.webhook_secret', 'whsec_test');

        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $this->cartFor($user, $variant, 2);

        $this->actingAs($user)->post(route('checkout.store'), $this->shippingData());
        $order = Order::query()->firstOrFail();

        $this->withHeader('Stripe-Signature', 't='.time().',v1=invalida')
            ->postJson(route('stripe.webhook'), [
                'type' => 'checkout.session.completed',
                'data' => ['object' => [
                    'client_reference_id' => $order->number,
                    'payment_status' => 'paid',
                ]],
            ])
            ->assertStatus(400);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => PaymentStatus::Pending->value,
        ]);
    }

    public function test_order_page_is_private(): void
    {
        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->cartFor($user, $variant, 2);

        config()->set('stripe.secret', null);

        $this->actingAs($user)->post(route('checkout.store'), $this->shippingData());
        $order = Order::query()->firstOrFail();

        $this->actingAs($other)
            ->get(route('order.show', $order))
            ->assertNotFound();
    }
}
