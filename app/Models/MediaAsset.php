<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaAsset extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $appends = [
        'public_url',
    ];

    public function getPublicUrlAttribute(): string
    {
        $appUrl = rtrim(config('app.url') ?? url('/'), '/');
        $storageUrl = ltrim(Storage::disk('public')->url($this->file_path), '/');

        return $storageUrl;
    }
}
