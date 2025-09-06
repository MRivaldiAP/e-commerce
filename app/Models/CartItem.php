<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    public function cart(): BelongsTo {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
