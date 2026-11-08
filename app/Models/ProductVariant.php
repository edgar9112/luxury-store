<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'color_hex',
        'price_cents',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function effectivePriceCents(): int
    {
        return $this->price_cents ?? $this->product->price_cents;
    }

    public function name(): string
    {
        $parts = array_filter([$this->size, $this->color]);

        return $parts ? implode(' / ', $parts) : 'Único';
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
