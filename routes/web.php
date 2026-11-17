<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeWebhookController;
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

Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');

Route::get('/producto/{product}', [ProductController::class, 'show'])->name('product.show');

Route::post('/bolsa', [CartController::class, 'store'])->name('cart.store');
Route::get('/bolsa', [CartController::class, 'index'])->name('cart.index');
Route::patch('/bolsa/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/bolsa/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/confirmado/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancelado/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

Route::get('/pedido/{order}', [OrderController::class, 'show'])->name('order.show');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
