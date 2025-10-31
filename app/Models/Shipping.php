<?php

namespace App\Models;

use App\Models\Order;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends TenantModel
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
        'remote_id',
        'meta',
    ];

    protected $casts = [
        'cost' => 'float',
        'estimated_delivery' => 'date',
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
