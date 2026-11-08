<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Moneda de la tienda
    |--------------------------------------------------------------------------
    |
    | Todos los importes se guardan en céntimos (price_cents). Aquí se define
    | cómo se muestran y qué moneda se envía a Stripe.
    |
    */

    'currency' => env('STORE_CURRENCY', 'USD'),

    'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '$'),

    /*
    |--------------------------------------------------------------------------
    | Envío
    |--------------------------------------------------------------------------
    |
    | Importes en céntimos. Si el subtotal alcanza el umbral, el envío es gratis.
    |
    */

    'shipping_cents' => (int) env('STORE_SHIPPING_CENTS', 495),

    'free_shipping_threshold_cents' => (int) env('STORE_FREE_SHIPPING_CENTS', 20000),

    /*
    |--------------------------------------------------------------------------
    | País por defecto del checkout
    |--------------------------------------------------------------------------
    */

    'country' => env('STORE_COUNTRY', 'US'),

];
