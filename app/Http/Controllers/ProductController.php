<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'images', 'activeVariants']);

        $related = Product::query()
            ->active()
            ->whereKeyNot($product->getKey())
            ->with('category')
            ->orderByRaw('category_id = ? desc', [$product->category_id])
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        return view('producto', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
