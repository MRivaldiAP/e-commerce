<?php

namespace App\Models;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Comment;
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
}
