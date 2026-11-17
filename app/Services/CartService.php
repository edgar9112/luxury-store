<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;

final class CartService
{
    /**
     * Bolsa del visitante o del usuario autenticado, si existe.
     */
    public function find(Request $request): ?Cart
    {
        if ($request->user()) {
            return Cart::query()->where('user_id', $request->user()->id)->first();
        }

        return Cart::query()->where('session_id', $request->session()->getId())->first();
    }

    /**
     * Bolsa del visitante o del usuario autenticado, creándola si hace falta.
     */
    public function resolve(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::query()->firstOrCreate([
                'user_id' => $request->user()->id,
                'session_id' => null,
            ]);
        }

        return Cart::query()->firstOrCreate([
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Importes en céntimos: subtotal, envío, descuento y total.
     *
     * @return array{subtotal: int, shipping: int, discount: int, total: int, item_count: int}
     */
    public function totals(Cart $cart, ?Coupon $coupon = null): array
    {
        $subtotal = $cart->subtotalCents();
        $itemCount = $cart->itemCount();
        $shipping = $this->shippingFor($subtotal, $itemCount);
        $discount = $coupon?->discountFor($subtotal) ?? 0;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount) + $shipping,
            'item_count' => $itemCount,
        ];
    }

    /**
     * Envío gratis al alcanzar el umbral y sin cargo en bolsas vacías.
     */
    public function shippingFor(int $subtotal, int $itemCount): int
    {
        if ($itemCount === 0 || $subtotal <= 0) {
            return 0;
        }

        $threshold = (int) config('store.free_shipping_threshold_cents');

        if ($threshold > 0 && $subtotal >= $threshold) {
            return 0;
        }

        return (int) config('store.shipping_cents');
    }
}
