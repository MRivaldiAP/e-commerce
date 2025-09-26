<?php

namespace App\Support;

use App\Models\PageSetting;

class LayoutSettings
{
    /**
     * @var array<string, array<string, string|null>>
     */
    protected static array $settingsCache = [];

    /**
     * Get raw layout settings for a theme.
     */
    public static function get(string $theme): array
    {
        if (! array_key_exists($theme, self::$settingsCache)) {
            self::$settingsCache[$theme] = PageSetting::where('theme', $theme)
                ->where('page', 'layout')
                ->pluck('value', 'key')
                ->toArray();
        }

        return self::$settingsCache[$theme];
    }

    /**
     * Build navigation configuration from stored settings.
     */
    public static function navigation(string $theme): array
    {
        $settings = self::get($theme);

        $brandVisible = ($settings['navigation.brand.visible'] ?? '1') === '1';
        $brandLabel = $settings['navigation.brand.text'] ?? self::defaultBrandLabel($theme);
        $brandLogo = self::storageAsset($settings['navigation.brand.logo'] ?? null);

        $links = [
            [
                'key' => 'home',
                'label' => 'Home',
                'href' => url('/'),
                'visible' => ($settings['navigation.link.home'] ?? '1') === '1',
            ],
            [
                'key' => 'products',
                'label' => 'Produk',
                'href' => route('products.index'),
                'visible' => ($settings['navigation.link.products'] ?? '1') === '1',
            ],
            [
                'key' => 'orders',
                'label' => 'Pesanan Saya',
                'href' => route('orders.index'),
                'visible' => ($settings['navigation.link.orders'] ?? '1') === '1',
            ],
        ];

        return [
            'brand' => [
                'visible' => $brandVisible,
                'label' => $brandLabel,
                'logo' => $brandLogo,
                'url' => url('/'),
            ],
            'links' => $links,
            'show_cart' => ($settings['navigation.icon.cart'] ?? '1') === '1',
            'show_login' => ($settings['navigation.button.login'] ?? '1') === '1',
        ];
    }

    /**
     * Build footer configuration from stored settings.
     */
    public static function footer(string $theme): array
    {
        $settings = self::get($theme);
        $navigation = self::navigation($theme);

        $links = array_values(array_filter($navigation['links'], function ($link) {
            return $link['visible'] ?? false;
        }));

        return [
            'show_hotlinks' => ($settings['footer.hotlinks.visible'] ?? '1') === '1',
            'links' => $links,
            'address' => [
                'visible' => ($settings['footer.address.visible'] ?? '1') === '1',
                'text' => $settings['footer.address.text'] ?? 'Jl. Herbal No. 1, Jakarta',
            ],
            'phone' => [
                'visible' => ($settings['footer.phone.visible'] ?? '1') === '1',
                'text' => $settings['footer.phone.text'] ?? '+62 811-1234-567',
            ],
            'email' => [
                'visible' => ($settings['footer.email.visible'] ?? '1') === '1',
                'text' => $settings['footer.email.text'] ?? 'hello@example.com',
            ],
            'social' => [
                'visible' => ($settings['footer.social.visible'] ?? '1') === '1',
                'text' => $settings['footer.social.text'] ?? 'https://instagram.com/yourstore',
            ],
            'schedule' => [
                'visible' => ($settings['footer.schedule.visible'] ?? '1') === '1',
                'text' => $settings['footer.schedule.text'] ?? 'Senin - Jumat: 09.00 - 18.00',
            ],
            'copyright' => $settings['footer.copyright'] ?? ('© ' . date('Y') . ' Herbal Green'),
        ];
    }

    protected static function storageAsset(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    protected static function defaultBrandLabel(string $theme): string
    {
        return match ($theme) {
            'theme-herbalgreen' => 'Herbal Green',
            'theme-restoran' => 'Restoran',
            'theme-second' => 'Ogani Store',
            default => 'Storefront',
        };
    }
}
