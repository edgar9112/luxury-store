<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'coupon_id',
        'status',
        'payment_status',
        'payment_method',
        'currency',
        'subtotal_cents',
        'shipping_cents',
        'discount_cents',
        'total_cents',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_line1',
        'shipping_line2',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_country',
        'stripe_session_id',
        'stripe_payment_intent',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal_cents' => 'integer',
            'shipping_cents' => 'integer',
            'discount_cents' => 'integer',
            'total_cents' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function totalFormatted(): string
    {
        return Money::format($this->total_cents, $this->currency);
    }

    public function shippingAddressLine(): string
    {
        return collect([
            $this->shipping_line1,
            $this->shipping_line2,
            $this->shipping_postal_code.' '.$this->shipping_city,
            $this->shipping_province,
            $this->shipping_country,
        ])->filter()->implode(', ');
    }
}
