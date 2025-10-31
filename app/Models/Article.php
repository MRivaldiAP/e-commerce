<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'excerpt',
        'content',
        'featured_image',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (empty($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->title ?? Str::random());
            }

            if (empty($article->meta_title) && ! empty($article->title)) {
                $article->meta_title = $article->title;
            }

            if (empty($article->published_at) && $article->is_published) {
                $article->published_at = now();
            }
        });

        static::updating(function (Article $article) {
            if (empty($article->meta_title) && ! empty($article->title)) {
                $article->meta_title = $article->title;
            }

            if ($article->is_published && empty($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug ?: Str::uuid()->toString();
    }
}
