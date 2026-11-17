<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe
    |--------------------------------------------------------------------------
    |
    | Claves de la cuenta de Stripe. Si `secret` está vacía, en local y en
    | tests se usa el pago de demostración; en producción el checkout avisa
    | de que falta configuración.
    |
    */

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

];
