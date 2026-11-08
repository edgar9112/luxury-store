<?php

namespace App\Support;

final class Money
{
    /**
     * Formatea céntimos como precio de tienda (ej. 485000 -> "4.850,00 €").
     */
    public static function format(int $cents, string $currency = 'EUR'): string
    {
        $symbol = match (strtoupper($currency)) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            default => strtoupper($currency),
        };

        return number_format($cents / 100, 2, ',', '.').' '.$symbol;
    }

    /**
     * Convierte céntimos al valor decimal que espera Stripe.
     */
    public static function toDecimal(int $cents): float
    {
        return round($cents / 100, 2);
    }
}
