<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
