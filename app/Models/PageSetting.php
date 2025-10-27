<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
{
    protected $fillable = ['theme', 'page', 'key', 'value'];

    /**
     * Retrieve settings for a page, optionally scoped to a theme.
     */
    public static function forPage(string $page, ?string $theme = null): array
    {
        $query = self::query()->where('page', $page);

        if ($theme !== null) {
            $query->where(function ($builder) use ($theme) {
                $builder->where('theme', $theme)
                    ->orWhereNull('theme');
            })->orderByRaw('CASE WHEN theme = ? THEN 1 ELSE 0 END', [$theme]);
        } else {
            $query->whereNull('theme');
        }

        return $query->pluck('value', 'key')->toArray();
    }

    /**
     * Persist a page setting.
     */
    public static function put(string $page, string $key, ?string $value, ?string $theme = null): void
    {
        $attributes = [
            'page' => $page,
            'key' => $key,
            'theme' => $theme ?: null,
        ];

        self::updateOrCreate($attributes, ['value' => $value]);
    }
}
