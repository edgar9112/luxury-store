<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Product, 1: ProductVariant}
     */
    private function createProduct(array $productOverrides = [], array $variantOverrides = []): array
    {
        $category = Category::create([
            'name' => 'Categoría de prueba',
            'slug' => 'categoria-'.Str::lower(Str::random(8)),
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $product = Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Reloj de Prueba',
            'slug' => 'reloj-de-prueba-'.Str::lower(Str::random(8)),
            'sku' => 'TEST-'.Str::upper(Str::random(8)),
            'short_description' => 'Pieza de prueba con materiales nobles.',
            'description' => 'Descripción completa de la pieza de prueba.',
            'price_cents' => 123400,
            'material' => 'Oro',
            'collection' => 'Pruebas',
            'is_active' => true,
        ], $productOverrides));

        $variant = ProductVariant::create(array_merge([
            'product_id' => $product->id,
            'sku' => 'TEST-VAR-'.Str::upper(Str::random(8)),
            'size' => '40 mm',
            'color' => 'Oro',
            'color_hex' => '#c9a961',
            'stock' => 5,
            'is_active' => true,
        ], $variantOverrides));

        return [$product, $variant];
    }

    private function cartFor(User $user, ProductVariant $variant, int $quantity = 1): Cart
    {
        $cart = Cart::create(['user_id' => $user->id]);

        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    public function test_cart_page_lists_items_and_totals(): void
    {
        [$product, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $this->cartFor($user, $variant, 2);

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('40 mm / Oro')
            ->assertSee('$2,468.00')
            ->assertSee('Gratis')
            ->assertSee('Finalizar compra');
    }

    public function test_empty_cart_shows_the_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Tu bolsa está vacía')
            ->assertSee('Ver el catálogo')
            ->assertDontSee('Finalizar compra');
    }

    public function test_authenticated_user_can_update_the_quantity(): void
    {
        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 3);

        $item = $cart->items()->first();

        $this->actingAs($user)
            ->patch(route('cart.update', $item), ['quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_quantity_update_is_limited_to_available_stock(): void
    {
        [, $variant] = $this->createProduct([], ['stock' => 2]);
        $user = User::factory()->create();
        $cart = $this->cartFor($user, $variant, 1);

        $item = $cart->items()->first();

        $this->actingAs($user)
            ->patch(route('cart.update', $item), ['quantity' => 9])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_cannot_update_an_item_from_another_cart(): void
    {
        [, $variant] = $this->createProduct();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $item = $this->cartFor($owner, $variant)->items()->first();

        $this->actingAs($intruder)
            ->patch(route('cart.update', $item), ['quantity' => 2])
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 1,
        ]);
    }

    public function test_user_can_remove_an_item_from_the_cart(): void
    {
        [, $variant] = $this->createProduct();
        $user = User::factory()->create();
        $item = $this->cartFor($user, $variant)->items()->first();

        $this->actingAs($user)
            ->delete(route('cart.destroy', $item))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }
}
