<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CatalogController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        [$sortColumn, $sortDirection] = $this->sort($filters['orden']);

        $products = Product::query()
            ->active()
            ->with('category')
            ->when($filters['categoria'] !== '', fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $category) => $category
                    ->where('slug', $filters['categoria'])
                    ->where('is_active', true)
            ))
            ->when($filters['talla'] !== '', fn (Builder $query) => $query->whereHas(
                'activeVariants',
                fn (Builder $variant) => $variant
                    ->where('size', $filters['talla'])
                    ->where('stock', '>', 0)
            ))
            ->when($filters['color'] !== '', fn (Builder $query) => $query->whereHas(
                'activeVariants',
                fn (Builder $variant) => $variant
                    ->where('color', $filters['color'])
                    ->where('stock', '>', 0)
            ))
            ->when(
                $filters['precio_min_cents'] !== null,
                fn (Builder $query) => $query->where('price_cents', '>=', $filters['precio_min_cents'])
            )
            ->when(
                $filters['precio_max_cents'] !== null,
                fn (Builder $query) => $query->where('price_cents', '<=', $filters['precio_max_cents'])
            )
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('catalogo', [
            'products' => $products,
            'categories' => $this->categories(),
            'sizes' => $this->sizes(),
            'colors' => $this->colors(),
            'filters' => $filters,
        ]);
    }

    private function filters(Request $request): array
    {
        $min = $request->filled('precio_min')
            ? max(0, $request->integer('precio_min'))
            : null;
        $max = $request->filled('precio_max')
            ? max(0, $request->integer('precio_max'))
            : null;

        // Si el usuario invierte el rango, lo corregimos.
        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        return [
            'categoria' => $request->string('categoria')->trim()->toString(),
            'talla' => $request->string('talla')->trim()->toString(),
            'color' => $request->string('color')->trim()->toString(),
            'precio_min' => $min,
            'precio_max' => $max,
            'precio_min_cents' => $min !== null ? $min * 100 : null,
            'precio_max_cents' => $max !== null ? $max * 100 : null,
            'orden' => $this->sortKey($request->string('orden')->trim()->toString()),
        ];
    }

    /**
     * Solo aceptamos claves de orden conocidas.
     */
    private function sortKey(string $order): string
    {
        return in_array($order, ['destacados', 'precio-asc', 'precio-desc', 'novedades'], true)
            ? $order
            : 'destacados';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function sort(string $order): array
    {
        return match ($order) {
            'precio-asc' => ['price_cents', 'asc'],
            'precio-desc' => ['price_cents', 'desc'],
            'novedades' => ['created_at', 'desc'],
            default => ['sort_order', 'asc']
        };
    }

    private function categories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->withCount('activeProducts as products_count')
            ->orderBy('sort_order')
            ->get();
    }

    private function sizes(): Collection
    {
        $sizes = ProductVariant::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereHas('product', fn (Builder $query) => $query->where('is_active', true))
            ->whereNotNull('size')
            ->distinct()
            ->pluck('size');

        return $this->sortSizes($sizes);
    }

    private function colors(): Collection
    {
        return ProductVariant::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereHas('product', fn (Builder $query) => $query->where('is_active', true))
            ->whereNotNull('color')
            ->select(['color', 'color_hex'])
            ->distinct()
            ->get()
            ->unique('color')
            ->sortBy('color', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Ordena tallas de ropa y medidas numéricas de forma natural.
     */
    private function sortSizes(Collection $sizes): Collection
    {
        $apparel = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6];

        return $sizes
            ->unique()
            ->sortBy(function (string $size) use ($apparel): int {
                if (isset($apparel[$size])) {
                    return 100 + $apparel[$size];
                }
                if (preg_match('/(\d+)/', $size, $matches)) {
                    return 200 + (int) $matches[1];
                }

                return 300;
            })->values();
    }
}
