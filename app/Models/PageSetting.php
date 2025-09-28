<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
{
    public const SHARED_THEME = '__shared__';

    protected $fillable = ['theme', 'page', 'key', 'value'];

    /**
     * Retrieve settings for a page with shared fallbacks.
     */
    public static function forPage(string $page, string $theme): array
    {
        $specific = self::query()
            ->where('theme', $theme)
            ->where('page', $page)
            ->pluck('value', 'key')
            ->toArray();

        $shared = self::query()
            ->where('theme', self::SHARED_THEME)
            ->where('page', $page)
            ->pluck('value', 'key')
            ->toArray();

        if (empty($shared)) {
            $shared = self::query()
                ->where('page', $page)
                ->where('theme', '!=', self::SHARED_THEME)
                ->orderByDesc('updated_at')
                ->get(['key', 'value'])
                ->reduce(function (array $carry, self $setting) {
                    if (! array_key_exists($setting->key, $carry)) {
                        $carry[$setting->key] = $setting->value;
                    }

                    return $carry;
                }, []);
        }

        if (empty($shared)) {
            return $specific;
        }

        return array_merge($shared, $specific);
    }

    /**
     * Persist a value to the shared storage for a page.
     */
    public static function storeShared(string $page, string $key, ?string $value): void
    {
        self::updateOrCreate(
            ['theme' => self::SHARED_THEME, 'page' => $page, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Persist a page setting for a theme and optionally mirror it to the shared store.
     */
    public static function put(string $theme, string $page, string $key, ?string $value, bool $share = true): void
    {
        self::updateOrCreate(
            ['theme' => $theme, 'page' => $page, 'key' => $key],
            ['value' => $value]
        );

        if ($share) {
            self::storeShared($page, $key, $value);
        }
    }
}
