<?php

namespace App\Models;

use App\Models\User;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne {
        return $this->hasOne(Payment::class);
    }

    public function shipping(): HasOne {
        return $this->hasOne(Shipping::class);
    }
}
