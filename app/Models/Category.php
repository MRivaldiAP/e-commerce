<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends TenantModel
{
    use HasFactory;

    protected $guarded = [];

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
