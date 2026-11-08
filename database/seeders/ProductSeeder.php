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
            // RESTO DEL CATÁLOGO DEL PROTOTIPO
            // ============================================================
            [
                'category' => 'relojes',
                'name' => 'Reloj Nocturne Acero',
                'sku' => 'LS-REL-002',
                'short_description' => 'Reloj automático de acero con esfera azul noche y brazalete integrado.',
                'description' => 'Reloj automático de 41 mm con caja y brazalete de acero 316L, esfera azul noche con acabado soleil y cristal de zafiro antirreflejo. Resistente al agua hasta 100 m y con reserva de marcha de 42 horas.',
                'price_cents' => 275000,
                'compare_price_cents' => null,
                'material' => 'Acero',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 3,
                'variants' => [
                    ['sku' => 'LS-REL-002-38', 'size' => '38 mm', 'color' => 'Acero', 'color_hex' => '#8a8a8a', 'stock' => 3],
                    ['sku' => 'LS-REL-002-40', 'size' => '40 mm', 'color' => 'Acero', 'color_hex' => '#8a8a8a', 'stock' => 4],
                    ['sku' => 'LS-REL-002-42', 'size' => '42 mm', 'color' => 'Acero', 'color_hex' => '#8a8a8a', 'stock' => 2],
                ],
            ],

            [
                'category' => 'relojes',
                'name' => 'Reloj Skeleton Noir',
                'sku' => 'LS-REL-003',
                'short_description' => 'Esqueleto de edición limitada con caja de oro ébano y mecanismo visible.',
                'description' => 'Pieza de colección con movimiento esqueleto de carga manual, caja de oro ébano de 42 mm y cristal de zafiro por ambas caras. Edición numerada de 50 unidades.',
                'price_cents' => 620000,
                'compare_price_cents' => null,
                'material' => 'Oro ébano',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 4,
                'variants' => [
                    ['sku' => 'LS-REL-003-40', 'size' => '40 mm', 'color' => 'Oro ébano', 'color_hex' => '#3b2f2a', 'stock' => 1],
                    ['sku' => 'LS-REL-003-42', 'size' => '42 mm', 'color' => 'Oro ébano', 'color_hex' => '#3b2f2a', 'stock' => 2],
                    ['sku' => 'LS-REL-003-44', 'size' => '44 mm', 'color' => 'Oro ébano', 'color_hex' => '#3b2f2a', 'stock' => 0],
                ],
            ],

            [
                'category' => 'ropa',
                'name' => 'Camisa Seda Marfil',
                'sku' => 'LS-ROP-002',
                'short_description' => 'Camisa de seda con cuello italiano y botones nacarados.',
                'description' => 'Camisa confeccionada en seda 100% con caída fluida, cuello italiano y botones de nácar. Corte relajado y acabados a mano.',
                'price_cents' => 29000,
                'compare_price_cents' => null,
                'material' => 'Seda',
                'collection' => 'Otoño 2026',
                'is_featured' => false,
                'sort_order' => 5,
                'variants' => [
                    ['sku' => 'LS-ROP-002-XS', 'size' => 'XS', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 2],
                    ['sku' => 'LS-ROP-002-S', 'size' => 'S', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 4],
                    ['sku' => 'LS-ROP-002-M', 'size' => 'M', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 5],
                    ['sku' => 'LS-ROP-002-L', 'size' => 'L', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 3],
                    ['sku' => 'LS-ROP-002-XL', 'size' => 'XL', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 1],
                ],
            ],

            [
                'category' => 'calzado',
                'name' => 'Zapatilla Aurelia',
                'sku' => 'LS-CAL-001',
                'short_description' => 'Zapatilla minimalista de piel marfil con suela de goma natural.',
                'description' => 'Zapatilla de perfil bajo confeccionada en piel de becerro marfil, forro de piel y suela de goma natural. Cosida a mano en Europa.',
                'price_cents' => 18900,
                'compare_price_cents' => null,
                'material' => 'Piel',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 6,
                'variants' => [
                    ['sku' => 'LS-CAL-001-39', 'size' => '39', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 2],
                    ['sku' => 'LS-CAL-001-40', 'size' => '40', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 3],
                    ['sku' => 'LS-CAL-001-41', 'size' => '41', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 4],
                    ['sku' => 'LS-CAL-001-42', 'size' => '42', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 3],
                    ['sku' => 'LS-CAL-001-43', 'size' => '43', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 2],
                    ['sku' => 'LS-CAL-001-44', 'size' => '44', 'color' => 'Marfil', 'color_hex' => '#f3ead7', 'stock' => 1],
                ],
            ],

            [
                'category' => 'calzado',
                'name' => 'Botín Siena',
                'sku' => 'LS-CAL-002',
                'short_description' => 'Botín de piel coñac con suela de cuero y acabado artesanal.',
                'description' => 'Botín de caña media en piel curtida al vegetal, suela de cuero cosida y acabado envejecido a mano. Un clásico que gana carácter con el uso.',
                'price_cents' => 25900,
                'compare_price_cents' => null,
                'material' => 'Piel',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 7,
                'variants' => [
                    ['sku' => 'LS-CAL-002-39', 'size' => '39', 'color' => 'Coñac', 'color_hex' => '#8b4a2b', 'stock' => 1],
                    ['sku' => 'LS-CAL-002-40', 'size' => '40', 'color' => 'Coñac', 'color_hex' => '#8b4a2b', 'stock' => 2],
                    ['sku' => 'LS-CAL-002-41', 'size' => '41', 'color' => 'Coñac', 'color_hex' => '#8b4a2b', 'stock' => 3],
                    ['sku' => 'LS-CAL-002-42', 'size' => '42', 'color' => 'Coñac', 'color_hex' => '#8b4a2b', 'stock' => 3],
                    ['sku' => 'LS-CAL-002-43', 'size' => '43', 'color' => 'Coñac', 'color_hex' => '#8b4a2b', 'stock' => 2],
                    ['sku' => 'LS-CAL-002-44', 'size' => '44', 'color' => 'Coñac', 'color_hex' => '#8b4a2b', 'stock' => 0],
                ],
            ],

            [
                'category' => 'accesorios',
                'name' => 'Bolso Piel Oro',
                'sku' => 'LS-ACC-001',
                'short_description' => 'Bolso estructurado de piel con herrajes en tono oro.',
                'description' => 'Bolso de mano de piel plena flor con cierre de solapa, forro de gamuza y herrajes bañados en oro. Incluye bandolera desmontable.',
                'price_cents' => 27900,
                'compare_price_cents' => null,
                'material' => 'Piel',
                'collection' => 'Otoño 2026',
                'is_featured' => true,
                'sort_order' => 8,
                'variants' => [
                    ['sku' => 'LS-ACC-001-UNI', 'size' => null, 'color' => 'Oro', 'color_hex' => '#c9a961', 'stock' => 6],
                ],
            ],

            [
                'category' => 'accesorios',
                'name' => 'Cinturón Piel Noir',
                'sku' => 'LS-ACC-002',
                'short_description' => 'Cinturón de piel con hebilla minimalista en tono latón.',
                'description' => 'Cinturón de piel curtida al vegetal de 3 cm, hebilla de latón cepillado y acabado teñido a mano.',
                'price_cents' => 14500,
                'compare_price_cents' => null,
                'material' => 'Piel',
                'collection' => 'Otoño 2026',
                'is_featured' => false,
                'sort_order' => 9,
                'variants' => [
                    ['sku' => 'LS-ACC-002-85', 'size' => '85 cm', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 3],
                    ['sku' => 'LS-ACC-002-90', 'size' => '90 cm', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 4],
                    ['sku' => 'LS-ACC-002-95', 'size' => '95 cm', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 3],
                    ['sku' => 'LS-ACC-002-100', 'size' => '100 cm', 'color' => 'Ébano', 'color_hex' => '#2c2418', 'stock' => 2],
                ],
            ],

        ];
    }
}
