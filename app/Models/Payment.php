<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'status',
        'transaction_id',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
