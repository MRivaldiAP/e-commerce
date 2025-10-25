<?php

namespace App\Models;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Comment;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function images(): HasMany {
        return $this->hasMany(ProductImage::class);
    }

    public function categories(): BelongsToMany {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function brand(): BelongsTo {
        return $this->belongsTo(Brand::class);
    }

    public function cartItems(): HasMany {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function comments(): HasMany {
        return $this->hasMany(Comment::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product')->withTimestamps();
    }

    public function activePromotions(?Carbon $at = null)
    {
        $at = $at ?: Carbon::now();

        if ($this->relationLoaded('promotions')) {
            return $this->promotions
                ->filter(fn (Promotion $promotion) => $promotion->isActive($at));
        }

        return $this->promotions()
            ->where(function ($query) use ($at) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function ($query) use ($at) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            })
            ->get();
    }

    public function currentPromotion(?Carbon $at = null): ?Promotion
    {
        $promotions = $this->activePromotions($at);

        if ($promotions instanceof \Illuminate\Database\Eloquent\Collection) {
            return $promotions
                ->sortByDesc(fn (Promotion $promotion) => $promotion->discountAmountFor($this->price))
                ->first();
        }

        return null;
    }

    public function getPromoPriceAttribute(): ?float
    {
        $promotion = $this->currentPromotion();

        if (! $promotion) {
            return null;
        }

        return $promotion->applyDiscount((float) $this->price);
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->promo_price ?? (float) $this->price;
    }

    public function getPromoLabelAttribute(): ?string
    {
        $promotion = $this->currentPromotion();

        return $promotion?->label;
    }
}
