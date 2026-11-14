<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductPageTest extends TestCase
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
            'stock' => 3,
            'is_active' => true,
        ], $variantOverrides));

        return [$product, $variant];
    }

    public function test_product_page_shows_details_and_price_in_usd(): void
    {
        [$product] = $this->createProduct();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('$1,234.00')
            ->assertSee('Añadir a la bolsa')
            ->assertSee($product->sku);
    }

    public function test_inactive_product_returns_not_found(): void
    {
        [$product] = $this->createProduct(['is_active' => false]);

        $this->get(route('product.show', $product))->assertNotFound();
    }

    public function test_guest_can_add_a_variant_to_the_cart(): void
    {
        [$product, $variant] = $this->createProduct();

        $this->post(route('cart.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect(route('product.show', $product));

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_quantity_is_limited_to_available_stock(): void
    {
        [$product, $variant] = $this->createProduct([], ['stock' => 2]);

        $this->post(route('cart.store'), [
            'variant_id' => $variant->id,
            'quantity' => 5,
        ])->assertRedirect(route('product.show', $product));

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_out_of_stock_variant_is_rejected(): void
    {
        [, $variant] = $this->createProduct([], ['stock' => 0]);

        $this->post(route('cart.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);
    }
}
