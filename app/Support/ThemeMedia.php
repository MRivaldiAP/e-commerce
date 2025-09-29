<?php

namespace App\Support;

use Illuminate\Support\Str;

class ThemeMedia
{
    public static function url(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if (Str::startsWith($trimmed, ['http://', 'https://', '//'])) {
            return $trimmed;
        }

        $normalized = ltrim($trimmed, '/');
        $normalized = preg_replace('/^storage\//', '', $normalized);

        return asset('storage/' . $normalized);
    }
}
