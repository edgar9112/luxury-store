<?php

namespace App\Enums;

enum CouponType: string
{
    case Percentage = 'percent';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Porcentaje',
            self::Fixed => 'Importe fijo',
        };
    }
}
