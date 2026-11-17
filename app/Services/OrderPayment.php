<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

final class OrderPayment
{
    /**
     * Confirma el cobro, libera la bolsa y descuenta la reserva de stock.
     */
    public function markPaid(
        Order $order,
        ?string $sessionId = null,
        ?string $paymentIntent = null,
        string $method = 'stripe'
    ): bool {
        return DB::transaction(function () use ($order, $sessionId, $paymentIntent, $method) {
            $current = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($current->payment_status === PaymentStatus::Paid) {
                return false;
            }

            $current->forceFill([
                'status' => OrderStatus::Paid,
                'payment_status' => PaymentStatus::Paid,
                'payment_method' => $method,
                'stripe_session_id' => $sessionId ?? $current->stripe_session_id,
                'stripe_payment_intent' => $paymentIntent ?? $current->stripe_payment_intent,
                'paid_at' => $current->paid_at ?? now(),
            ])->save();

            $cart = $current->cart;

            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }

            return true;
        });
    }

    /**
     * Cancela un pedido sin pagar y devuelve el stock y el uso del cupón.
     */
    public function markCancelled(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $current = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($current->payment_status === PaymentStatus::Paid || $current->status === OrderStatus::Cancelled) {
                return false;
            }

            $current->forceFill([
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Failed,
            ])->save();

            foreach ($current->items as $item) {
                if ($item->product_variant_id !== null) {
                    ProductVariant::query()
                        ->whereKey($item->product_variant_id)
                        ->increment('stock', $item->quantity);
                }
            }

            if ($current->coupon_id !== null) {
                Coupon::query()
                    ->whereKey($current->coupon_id)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            return true;
        });
    }
}
