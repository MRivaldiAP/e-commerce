<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return asset('storage/' . ltrim($this->file_path, '/'));
    }
}
