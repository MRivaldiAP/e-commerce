<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
{
    protected $fillable = ['page', 'key', 'value'];

    /**
     * Retrieve settings for a page.
     */
    public static function forPage(string $page): array
    {
        return self::query()
            ->where('page', $page)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Persist a page setting.
     */
    public static function put(string $page, string $key, ?string $value): void
    {
        self::updateOrCreate(
            ['page' => $page, 'key' => $key],
            ['value' => $value]
        );
    }
}
