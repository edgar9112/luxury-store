<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class StripeCheckout
{
    private const API_URL = 'https://api.stripe.com/v1';

    public function configured(): bool
    {
        return filled(config('stripe.secret'));
    }

    /**
     * Crea una sesión de pago alojada en Stripe.
     *
     * @return array{id: string, url: string}
     */
    public function createSession(Order $order, string $successUrl, string $cancelUrl): array
    {
        $form = [
            'mode' => 'payment',
            'client_reference_id' => $order->number,
            'customer_email' => $order->customer_email,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => 'es',
            'metadata[order_number]' => $order->number,
        ];

        foreach ($order->items as $index => $item) {
            $name = $item->product_name.($item->variant_name ? ' · '.$item->variant_name : '');
            $form["line_items[$index][price_data][currency]"] = strtolower($order->currency);
            $form["line_items[$index][price_data][product_data][name]"] = $name;
            $form["line_items[$index][price_data][unit_amount]"] = $item->unit_price_cents;
            $form["line_items[$index][quantity]"] = $item->quantity;
        }

        $shippingIndex = $order->items->count();

        if ($order->shipping_cents > 0) {
            $form["line_items[$shippingIndex][price_data][currency]"] = strtolower($order->currency);
            $form["line_items[$shippingIndex][price_data][product_data][name]"] = 'Envío asegurado';
            $form["line_items[$shippingIndex][price_data][unit_amount]"] = $order->shipping_cents;
            $form["line_items[$shippingIndex][quantity]"] = 1;
        }

        if ($order->discount_cents > 0) {
            $coupon = $this->request()->post(self::API_URL.'/coupons', [
                'amount_off' => $order->discount_cents,
                'currency' => strtolower($order->currency),
                'duration' => 'once',
                'name' => 'Descuento '.$order->number,
            ]);

            if (! $coupon->successful() || ! filled($coupon->json('id'))) {
                throw new RuntimeException('No se pudo crear el descuento en Stripe.');
            }

            $form['discounts[0][coupon]'] = $coupon->json('id');
        }

        $response = $this->request()->post(self::API_URL.'/checkout/sessions', $form);

        if (! $response->successful() || ! filled($response->json('id')) || ! filled($response->json('url'))) {
            throw new RuntimeException(
                'Stripe rechazó la sesión de pago: '.$response->json('error.message', 'error desconocido')
            );
        }

        return [
            'id' => (string) $response->json('id'),
            'url' => (string) $response->json('url'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function retrieveSession(string $sessionId): ?array
    {
        $response = $this->request()->get(self::API_URL.'/checkout/sessions/'.rawurlencode($sessionId));

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Verifica la firma del webhook y devuelve su payload.
     *
     * @return array<string, mixed>|null
     */
    public function verifyWebhook(Request $request): ?array
    {
        $secret = (string) config('stripe.webhook_secret');

        if ($secret === '') {
            return null;
        }

        $timestamp = null;
        $signatures = [];

        preg_match_all('/(?:^|,)\s*([a-zA-Z0-9_]+)=([^,]+)/', (string) $request->header('Stripe-Signature', ''), $matches);

        foreach ($matches[1] as $index => $key) {
            $value = trim($matches[2][$index]);

            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || ! ctype_digit($timestamp) || $signatures === []) {
            return null;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return null;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        $valid = false;

        foreach ($signatures as $signature) {
            $valid = hash_equals($expected, $signature) || $valid;
        }

        if (! $valid) {
            return null;
        }

        $payload = $request->json()->all();

        return is_array($payload) ? $payload : null;
    }

    private function request()
    {
        return Http::withToken((string) config('stripe.secret'))->asForm()->acceptJson();
    }
}
