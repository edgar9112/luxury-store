<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'unit_price_cents',
        'quantity',
        'total_cents',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_cents' => 'integer',
            'quantity' => 'integer',
            'total_cents' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unitPriceFormatted(): string
    {
        return Money::format($this->unit_price_cents);
    }

    public function totalFormatted(): string
    {
        return Money::format($this->total_cents);
    }
}
