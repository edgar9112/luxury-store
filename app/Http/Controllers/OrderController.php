<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        if ($order->user_id !== null) {
            abort_unless($request->user()?->id === $order->user_id, 404);
        } else {
            abort_unless(session('last_order_number') === $order->number, 404);
        }

        $order->load(['items.variant.product', 'coupon']);

        return view('pedido', [
            'order' => $order,
        ]);
    }
}
