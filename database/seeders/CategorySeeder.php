<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->categories() as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Categorías del prototipo.
     *
     * @return array<int, array{name: string, description: string, sort_order: int}>
     */
    private function categories(): array
    {
        return [
            [
                'name' => 'Relojes',
                'description' => 'Alta relojería de edición limitada, con mecanismos suizos y acabados artesanales.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Ropa',
                'description' => 'Prendas de autor confeccionadas con materiales nobles: cashmere, seda y lana.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Calzado',
                'description' => 'Calzado de edición limitada, cosido a mano y terminado en Europa.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Accesorios',
                'description' => 'Bolsos, cinturones y pequeños detalles que completan el estilo.',
                'sort_order' => 4,
            ],
        ];
    }
}
