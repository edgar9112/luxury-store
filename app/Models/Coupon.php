<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_cents',
        'starts_at',
        'expires_at',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'integer',
            'min_order_cents' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isValidFor(int $subtotalCents): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return $subtotalCents >= $this->min_order_cents;
    }

    public function discountFor(int $subtotalCents): int
    {
        if (! $this->isValidFor($subtotalCents)) {
            return 0;
        }

        return match ($this->type) {
            CouponType::Percentage => (int) round($subtotalCents * $this->value / 100),
            CouponType::Fixed => min($this->value, $subtotalCents),
        };
    }

    public function formattedValue(): string
    {
        return $this->type === CouponType::Percentage
            ? $this->value.' %'
            : Money::format($this->value);
    }
}
