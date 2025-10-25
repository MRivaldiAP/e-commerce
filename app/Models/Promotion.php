<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount_value' => 'float',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product')->withTimestamps();
    }

    public function scopeActive($query)
    {
        $now = Carbon::now();

        return $query->where(function ($inner) use ($now) {
            $inner->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($inner) use ($now) {
            $inner->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        });
    }

    public function isActive(?Carbon $at = null): bool
    {
        $at = $at ?: Carbon::now();

        if ($this->starts_at && $at->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $at->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function discountAmountFor(float $price): float
    {
        $price = max(0, $price);

        if ($this->discount_type === self::TYPE_FIXED) {
            return min($price, (float) $this->discount_value);
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $percentage = max(0, min(100, (float) $this->discount_value));
            return round($price * ($percentage / 100), 2);
        }

        return 0.0;
    }

    public function applyDiscount(float $price): float
    {
        $amount = $this->discountAmountFor($price);

        return max(0, round($price - $amount, 2));
    }

    public function getLabelAttribute(): string
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return sprintf('Diskon %s%%', rtrim(rtrim(number_format($this->discount_value, 2, '.', ''), '0'), '.'));
        }

        return sprintf('Potongan Rp %s', number_format($this->discount_value, 0, ',', '.'));
    }
}
