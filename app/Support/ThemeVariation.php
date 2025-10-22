<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ThemeVariation
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected static array $definitionCache = [];

    /**
     * Load the variation definitions for a theme.
     */
    public static function definitions(string $theme): array
    {
        if (! array_key_exists($theme, self::$definitionCache)) {
            $path = base_path("themes/{$theme}/variations.php");
            self::$definitionCache[$theme] = File::exists($path) ? include $path : [];
        }

        return self::$definitionCache[$theme];
    }

    /**
     * Get a map of variation keys and their display labels.
     */
    public static function options(string $theme): array
    {
        $definitions = self::definitions($theme);

        if ($definitions === []) {
            $fallback = self::fallback();

            return ['default' => $fallback['label']];
        }

        $options = [];
        foreach ($definitions as $key => $config) {
            $options[$key] = $config['label'] ?? ucfirst(str_replace(['-', '_'], ' ', $key));
        }

        return $options;
    }

    /**
     * Determine the default variation key for a theme.
     */
    public static function defaultKey(string $theme): string
    {
        $definitions = self::definitions($theme);

        if ($definitions === []) {
            return 'default';
        }

        foreach ($definitions as $key => $config) {
            if (! empty($config['default'])) {
                return $key;
            }
        }

        return array_key_first($definitions);
    }

    /**
     * Ensure the provided key exists in the theme definitions.
     */
    public static function ensureKey(string $theme, ?string $key): string
    {
        $definitions = self::definitions($theme);

        if ($definitions === []) {
            return 'default';
        }

        if ($key !== null && array_key_exists($key, $definitions)) {
            return $key;
        }

        return self::defaultKey($theme);
    }

    /**
     * Retrieve the configuration for a variation.
     */
    public static function get(string $theme, ?string $key = null): array
    {
        $definitions = self::definitions($theme);

        if ($definitions === []) {
            $fallback = self::fallback();

            return array_merge(['key' => 'default'], $fallback);
        }

        $resolvedKey = self::ensureKey($theme, $key);
        $config = $definitions[$resolvedKey] ?? [];

        return array_merge(['key' => $resolvedKey], $config);
    }

    /**
     * Flush cached definitions for a theme.
     */
    public static function flush(?string $theme = null): void
    {
        if ($theme === null) {
            self::$definitionCache = [];

            return;
        }

        unset(self::$definitionCache[$theme]);
    }

    /**
     * Provide a fallback variation configuration when no definitions exist.
     */
    protected static function fallback(): array
    {
        return [
            'label' => 'Default Palette',
            'colors' => [
                'primary' => '#FEA116',
                'primary_rgb' => '254, 161, 22',
                'accent' => '#0F172B',
                'accent_rgb' => '15, 23, 43',
                'light' => '#FFFFFF',
                'light_rgb' => '255, 255, 255',
                'background' => '#FFFFFF',
                'text' => '#212529',
                'contrast' => '#FFFFFF',
            ],
        ];
    }
}
