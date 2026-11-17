<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPayment;
use App\Services\StripeCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    private const PAID_EVENTS = [
        'checkout.session.completed',
        'checkout.session.async_payment_succeeded',
    ];

    private const CANCELLED_EVENTS = [
        'checkout.session.expired',
        'checkout.session.async_payment_failed',
    ];

    public function handle(Request $request, StripeCheckout $stripe, OrderPayment $payments): JsonResponse
    {
        $payload = $stripe->verifyWebhook($request);

        if ($payload === null) {
            return response()->json(['error' => 'Firma de webhook no válida.'], 400);
        }

        $type = $payload['type'] ?? null;
        $session = $payload['data']['object'] ?? [];
        $reference = is_string($session['client_reference_id'] ?? null) ? $session['client_reference_id'] : null;

        $order = $reference
            ? Order::query()->where('number', $reference)->first()
            : null;

        if ($order && in_array($type, self::PAID_EVENTS, true) && ($session['payment_status'] ?? null) === 'paid') {
            $intent = is_string($session['payment_intent'] ?? null) ? $session['payment_intent'] : null;
            $sessionId = is_string($session['id'] ?? null) ? $session['id'] : null;

            $payments->markPaid($order, $sessionId, $intent);
        }

        if ($order && in_array($type, self::CANCELLED_EVENTS, true)) {
            $payments->markCancelled($order);
        }

        return response()->json(['received' => true]);
    }
}
