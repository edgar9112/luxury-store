<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price_cents',
        'compare_price_cents',
        'material',
        'collection',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'compare_price_cents' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('size');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(): string
    {
        $image = $this->relationLoaded('primaryImage')
            ? $this->primaryImage
            : $this->primaryImage()->first();

        $image ??= $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $image?->url ?? asset('images/placeholder.svg');
    }

    public function totalStock(): int
    {
        return (int) $this->variants()->sum('stock');
    }

    public function formattedPrice(): string
    {
        return Money::format($this->price_cents);
    }

    public function formattedComparePrice(): ?string
    {
        return $this->compare_price_cents
            ? Money::format($this->compare_price_cents)
            : null;
    }
}
