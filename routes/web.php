<?php

use App\Http\Controllers\NewsletterController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'categories' => Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(),

        'featured' => Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['category', 'primaryImage'])
            ->orderBy('sort_order')
            ->take(4)
            ->get(),
    ]);
})->name('home');

Route::get('/catalogo', function () {
    return view('catalogo', [
        'navCategories' => Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(),
    ]);
})->name('catalog.index');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
