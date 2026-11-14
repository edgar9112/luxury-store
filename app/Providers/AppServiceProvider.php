<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view): void {
            $view->with('navCategories', Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get());

            $cart = auth()->check()
                ? Cart::query()->where('user_id', auth()->id())->first()
                : Cart::query()->where('session_id', session()->getId())->first();

            $view->with('cartCount', $cart ? (int) $cart->items()->sum('quantity') : 0);
        });
    }
}
