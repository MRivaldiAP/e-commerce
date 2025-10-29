<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
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

        return $appUrl . '/' . $storageUrl;
    }
}
