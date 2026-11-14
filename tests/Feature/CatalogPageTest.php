<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(string $name, int $sortOrder = 0, bool $active = true): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sort_order' => $sortOrder,
            'is_active' => $active,
        ]);
    }

    private function makeProduct(Category $category, string $name, int $priceCents, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'short_description' => 'Pieza de prueba con materiales nobles.',
            'description' => 'Descripción completa de la pieza de prueba.',
            'price_cents' => $priceCents,
            'material' => 'Oro',
            'collection' => 'Pruebas',
            'is_active' => true,
        ], $overrides));
    }

    private function makeVariant(
        Product $product,
        ?string $size = '40 mm',
        ?string $color = 'Oro',
        int $stock = 5,
        array $overrides = []
    ): ProductVariant {
        return ProductVariant::create(array_merge([
            'product_id' => $product->id,
            'sku' => 'VAR-'.Str::upper(Str::random(8)),
            'size' => $size,
            'color' => $color,
            'color_hex' => '#c9a961',
            'stock' => $stock,
            'is_active' => true,
        ], $overrides));
    }

    public function test_catalog_lists_only_active_products(): void
    {
        $category = $this->makeCategory('Relojes');

        $this->makeProduct($category, 'Reloj Visible', 100000);
        $this->makeProduct($category, 'Reloj Oculto', 100000, ['is_active' => false]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Reloj Visible')
            ->assertDontSee('Reloj Oculto')
            ->assertSee('1 pieza');
    }

    public function test_catalog_filters_by_category(): void
    {
        $relojes = $this->makeCategory('Relojes');
        $ropa = $this->makeCategory('Ropa');

        $this->makeProduct($relojes, 'Reloj de Oro', 300000);
        $this->makeProduct($ropa, 'Abrigo de Lana', 200000);

        $this->get(route('catalog.index', ['categoria' => $relojes->slug]))
            ->assertOk()
            ->assertSee('Reloj de Oro')
            ->assertDontSee('Abrigo de Lana');
    }

    public function test_catalog_filters_by_size_with_stock(): void
    {
        $category = $this->makeCategory('Ropa');

        $disponible = $this->makeProduct($category, 'Chaqueta Disponible', 150000);
        $this->makeVariant($disponible, '40 mm', 'Oro', 5);

        $agotada = $this->makeProduct($category, 'Chaqueta Agotada', 150000);
        $this->makeVariant($agotada, '40 mm', 'Oro', 0);

        $otraTalla = $this->makeProduct($category, 'Chaqueta Otra Talla', 150000);
        $this->makeVariant($otraTalla, '42 mm', 'Oro', 5);

        $this->get(route('catalog.index', ['talla' => '40 mm']))
            ->assertOk()
            ->assertSee('Chaqueta Disponible')
            ->assertDontSee('Chaqueta Agotada')
            ->assertDontSee('Chaqueta Otra Talla');
    }

    public function test_catalog_filters_by_color_with_stock(): void
    {
        $category = $this->makeCategory('Calzado');

        $oro = $this->makeProduct($category, 'Botín de Oro', 250000);
        $this->makeVariant($oro, '42', 'Oro', 3);

        $plata = $this->makeProduct($category, 'Botín de Plata', 250000);
        $this->makeVariant($plata, '42', 'Plata', 3);

        $this->get(route('catalog.index', ['color' => 'Oro']))
            ->assertOk()
            ->assertSee('Botín de Oro')
            ->assertDontSee('Botín de Plata');
    }

    public function test_catalog_filters_by_price_range_in_dollars(): void
    {
        $category = $this->makeCategory('Accesorios');

        $this->makeProduct($category, 'Pieza Barata', 10000);   // $100.00
        $this->makeProduct($category, 'Pieza Cara', 50000);     // $500.00

        $this->get(route('catalog.index', ['precio_min' => 200]))
            ->assertOk()
            ->assertSee('Pieza Cara')
            ->assertDontSee('Pieza Barata');

        $this->get(route('catalog.index', ['precio_max' => 200]))
            ->assertOk()
            ->assertSee('Pieza Barata')
            ->assertDontSee('Pieza Cara');
    }

    public function test_catalog_swaps_an_inverted_price_range(): void
    {
        $category = $this->makeCategory('Accesorios');

        $this->makeProduct($category, 'Pieza de Cien', 10000);
        $this->makeProduct($category, 'Pieza de Trescientos', 30000);
        $this->makeProduct($category, 'Pieza de Novecientos', 90000);

        // Se envía el rango al revés: el controlador debe corregirlo.
        $this->get(route('catalog.index', ['precio_min' => 500, 'precio_max' => 100]))
            ->assertOk()
            ->assertSee('Pieza de Cien')
            ->assertSee('Pieza de Trescientos')
            ->assertDontSee('Pieza de Novecientos');
    }

    public function test_catalog_sorts_by_price_ascending_and_descending(): void
    {
        $category = $this->makeCategory('Accesorios');

        $this->makeProduct($category, 'Pieza Económica', 10000);
        $this->makeProduct($category, 'Pieza Media', 30000);
        $this->makeProduct($category, 'Pieza Premium', 90000);

        $this->get(route('catalog.index', ['orden' => 'precio-asc']))
            ->assertOk()
            ->assertSeeInOrder(['Pieza Económica', 'Pieza Media', 'Pieza Premium']);

        $this->get(route('catalog.index', ['orden' => 'precio-desc']))
            ->assertOk()
            ->assertSeeInOrder(['Pieza Premium', 'Pieza Media', 'Pieza Económica']);
    }

    public function test_catalog_sorts_by_newest_first(): void
    {
        $category = $this->makeCategory('Accesorios');

        $vieja = $this->makeProduct($category, 'Pieza Antigua', 10000);
        $vieja->forceFill(['created_at' => now()->subDays(5)])->save();

        $nueva = $this->makeProduct($category, 'Pieza Reciente', 10000);
        $nueva->forceFill(['created_at' => now()->subDay()])->save();

        $this->get(route('catalog.index', ['orden' => 'novedades']))
            ->assertOk()
            ->assertSeeInOrder(['Pieza Reciente', 'Pieza Antigua']);
    }

    public function test_catalog_falls_back_to_the_default_order_for_unknown_sort_keys(): void
    {
        $category = $this->makeCategory('Accesorios');

        $this->makeProduct($category, 'Pieza Primera', 10000, ['sort_order' => 1]);
        $this->makeProduct($category, 'Pieza Segunda', 10000, ['sort_order' => 2]);

        $this->get(route('catalog.index', ['orden' => 'borrar-todo']))
            ->assertOk()
            ->assertSeeInOrder(['Pieza Primera', 'Pieza Segunda']);
    }

    public function test_catalog_paginates_twelve_products_and_keeps_filters_in_the_links(): void
    {
        $category = $this->makeCategory('Accesorios');

        for ($i = 1; $i <= 13; $i++) {
            $this->makeProduct($category, 'Accesorio '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 10000 + $i);
        }

        $this->get(route('catalog.index', ['categoria' => $category->slug]))
            ->assertOk()
            ->assertSee('13 piezas')
            ->assertViewHas('products', fn ($products) => $products->count() === 12 && $products->total() === 13)
            ->assertSee('categoria='.$category->slug, false);

        $this->get(route('catalog.index', ['categoria' => $category->slug, 'page' => 2]))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 1 && $products->total() === 13);
    }

    public function test_catalog_shows_an_empty_state_when_nothing_matches(): void
    {
        $category = $this->makeCategory('Accesorios');
        $this->makeProduct($category, 'Pieza Existente', 10000);

        $this->get(route('catalog.index', ['categoria' => 'no-existe']))
            ->assertOk()
            ->assertSee('Sin resultados')
            ->assertSee('0 piezas')
            ->assertDontSee('Pieza Existente');
    }

    public function test_catalog_renders_active_filters_as_chips(): void
    {
        $category = $this->makeCategory('Calzado');
        $product = $this->makeProduct($category, 'Botín de Cuero', 45000);
        $this->makeVariant($product, '42', 'Marrón', 4);

        $filters = [
            'categoria' => $category->slug,
            'talla' => '42',
            'color' => 'Marrón',
            'precio_min' => 100,
            'precio_max' => 600,
        ];

        $response = $this->get(route('catalog.index', $filters))->assertOk();

        $response
            ->assertSee('Categoría: '.$category->name)
            ->assertSee('Talla: 42')
            ->assertSee('Color: Marrón')
            ->assertSee('Precio: $100 – $600');

        // El chip de categoría conserva los demás filtros y elimina solo el suyo.
        $sinCategoria = route('catalog.index', [
            'talla' => '42',
            'color' => 'Marrón',
            'precio_min' => 100,
            'precio_max' => 600,
        ]);

        $response->assertSee('href="'.e($sinCategoria).'"', false);
    }
}
