<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductMedia extends TenantModel
{
    use HasFactory;

    /**
     * Get the product that owns the ProductMedia
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
