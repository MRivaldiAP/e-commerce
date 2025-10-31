<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'gallery_category_id',
        'title',
        'image_path',
        'description',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }
}
