<?php

namespace App\Support;

final class Money
{
    /**
     * Formatea céntimos como precio de tienda.
     *
     * USD -> "$1,234.56"   EUR -> "1.234,56 €"   GBP -> "£1,234.56"
     */
    public static function format(int $cents, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?? config('store.currency', 'USD'));

        return match ($currency) {
            'USD' => '$'.number_format($cents / 100, 2, '.', ','),
            'EUR' => number_format($cents / 100, 2, ',', '.').' €',
            'GBP' => '£'.number_format($cents / 100, 2, '.', ','),
            default => number_format($cents / 100, 2, '.', ',').' '.$currency,
        };
    }

    /**
     * Moneda activa de la tienda (código ISO de 3 letras).
     */
    public static function currency(): string
    {
        return strtoupper((string) config('store.currency', 'USD'));
    }

    /**
     * Símbolo de la moneda activa.
     */
    public static function symbol(): string
    {
        return (string) config('store.currency_symbol', '$');
    }

    /**
     * Convierte céntimos al valor decimal que espera Stripe.
     */
    public static function toDecimal(int $cents): float
    {
        return round($cents / 100, 2);
    }

    /**
     * Importe en la unidad mínima que espera Stripe (céntimos).
     */
    public static function toStripeAmount(int $cents): int
    {
        return $cents;
    }
}
