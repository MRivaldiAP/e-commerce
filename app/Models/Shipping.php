<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'courier',
        'service',
        'tracking_number',
        'external_id',
        'cost',
        'status',
        'estimated_delivery',
        'metadata',
    ];

    protected $casts = [
        'cost' => 'float',
        'estimated_delivery' => 'date',
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
