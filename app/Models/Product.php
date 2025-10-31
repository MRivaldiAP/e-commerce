<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Comment;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Product extends TenantModel
{
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

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

    public function activePromotions(?Carbon $at = null, ?User $user = null, bool $respectEligibility = true)
    {
        $at = $at ?: Carbon::now();

        if ($respectEligibility) {
            $user = $user ?: Auth::user();
        }

        if ($this->relationLoaded('promotions')) {
            $promotions = $this->promotions
                ->loadMissing('users')
                ->filter(fn (Promotion $promotion) => $promotion->isActive($at));
        } else {
            $promotions = $this->promotions()
                ->where(function ($query) use ($at) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
                })
                ->where(function ($query) use ($at) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
                })
                ->get()
                ->load('users');
        }

        if ($respectEligibility) {
            return $promotions->filter(fn (Promotion $promotion) => $promotion->isEligibleFor($user));
        }

        return $promotions;
    }

    public function currentPromotion(?Carbon $at = null, ?User $user = null, bool $respectEligibility = true): ?Promotion
    {
        $promotions = $this->activePromotions($at, $user, $respectEligibility);

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

    public function getPromoAudienceLabelAttribute(): ?string
    {
        $promotion = $this->currentPromotion(null, null, false);

        return $promotion?->audience_label;
    }
}
