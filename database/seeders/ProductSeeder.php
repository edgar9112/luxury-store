<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->products() as $data) {
            $category = Category::where('slug', $data['category'])->firstOrFail();

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'compare_price_cents' => $data['compare_price_cents'] ?? null,
                    'material' => $data['material'],
                    'collection' => $data['collection'],
                    'is_featured' => $data['is_featured'] ?? false,
                    'is_active' => true,
                    'sort_order' => $data['sort_order'] ?? 0,
                ]
            );

            foreach ($data['variants'] as $variant) {
                ProductVariant::updateOrCreate(
                    ['sku' => $variant['sku']],
                    [
                        'product_id' => $product->id,
                        'size' => $variant['size'] ?? null,
                        'color' => $variant['color'] ?? null,
                        'color_hex' => $variant['color_hex'] ?? null,
                        'price_cents' => $variant['price_cents'] ?? null,
                        'stock' => $variant['stock'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * Catálogo de productos.
     *
     * Los precios se guardan en CÉNTIMOS: $4,850.00 -> 485000.
     * `category` debe coincidir con el slug de CategorySeeder:
     * relojes, ropa, calzado, accesorios.
     *
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        return [
            // ============================================================
            // EJEMPLO 1 — Producto con variantes por diámetro (reloj)
            // ============================================================
            [
                'category' => 'relojes',
                'name' => 'Reloj Héritage Or',
                'sku' => 'LS-REL-001',
                'short_description' => 'Cronógrafo automático con caja de oro 18k y esfera marfil.',
                'description' => 'Una pieza de alta relojería de edición numerada. Movimiento automático suizo, caja de oro 18k, cristal de zafiro y correa de piel curtida a mano. Cada reloj se entrega con estuche de madera y certificado de autenticidad.',
                'price_cents' => 485000,
                'compare_price_cents' => 520000,
                'material' => 'Oro 18k',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 1,
                'variants' => [
                    ['sku' => 'LS-REL-001-36', 'size' => '36 mm', 'color' => 'Oro 18k', 'color_hex' => '#c9a961', 'stock' => 2],
                    ['sku' => 'LS-REL-001-38', 'size' => '38 mm', 'color' => 'Oro 18k', 'color_hex' => '#c9a961', 'stock' => 3],
                    ['sku' => 'LS-REL-001-40', 'size' => '40 mm', 'color' => 'Oro 18k', 'color_hex' => '#c9a961', 'stock' => 4],
                    ['sku' => 'LS-REL-001-42', 'size' => '42 mm', 'color' => 'Oro 18k', 'color_hex' => '#c9a961', 'stock' => 0],
                ],
            ],

            // ============================================================
            // EJEMPLO 2 — Producto con variantes por talla (ropa)
            // ============================================================
            [
                'category' => 'ropa',
                'name' => 'Abrigo Cashmere Noir',
                'sku' => 'LS-ROP-001',
                'short_description' => 'Abrigo largo de cashmere en color ébano, forro de seda.',
                'description' => 'Abrigo de silueta recta confeccionado en cashmere mongol de doble cara. Forro interior de seda, botonadura oculta y acabados cosidos a mano. Una prenda atemporal pensada para durar generaciones.',
                'price_cents' => 34900,
                'compare_price_cents' => null,
                'material' => 'Cashmere',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 2,
                'variants' => [
                    ['sku' => 'LS-ROP-001-XS', 'size' => 'XS', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 2],
                    ['sku' => 'LS-ROP-001-S', 'size' => 'S', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 4],
                    ['sku' => 'LS-ROP-001-M', 'size' => 'M', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 5],
                    ['sku' => 'LS-ROP-001-L', 'size' => 'L', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 3],
                    ['sku' => 'LS-ROP-001-XL', 'size' => 'XL', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 0],
                ],
            ],

            // ============================================================
            // TUS PRODUCTOS — añade aquí los 7 restantes del prototipo
            // ============================================================
            // Copia el bloque de arriba y cambia los datos.
            // Recuerda:
            //   - price_cents: precio en céntimos (349 € -> 34900)
            //   - category: relojes | ropa | calzado | accesorios
            //   - SKU único por producto y por variante
            //   - stock: unidades disponibles de cada variante
            //
            // Lista pendiente:
            //   Reloj Nocturne Acero      $2,750.00
            //   Reloj Skeleton Noir       $6,200.00
            //   Camisa Seda Marfil          $290.00
            //   Zapatilla Aurelia           $189.00
            //   Botín Siena                 $259.00
            //   Bolso Piel Oro              $279.00
            //   Cinturón Piel Noir          $145.00
        ];
    }
}
