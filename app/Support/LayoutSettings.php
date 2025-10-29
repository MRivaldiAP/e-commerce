<?php

namespace App\Support;

use App\Models\PageSetting;
use App\Models\Setting;
use function str_starts_with;

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
            self::$settingsCache[$theme] = PageSetting::forPage('layout');
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
        $brandIconSettingExists = array_key_exists('navigation.brand.icon', $settings);
        $brandIconRaw = $settings['navigation.brand.icon'] ?? null;
        $brandIcon = $brandIconRaw !== null ? trim((string) $brandIconRaw) : '';
        if ($brandIcon === '') {
            $brandIcon = $brandIconSettingExists ? null : 'fa fa-utensils';
        }

        $articleDetailUrl = self::firstArticleDetailUrl();

        $links = [
            [
                'key' => 'home',
                'label' => 'Home',
                'href' => url('/'),
                'visible' => ($settings['navigation.link.home'] ?? '1') === '1',
            ],
            [
                'key' => 'about',
                'label' => 'Tentang Kami',
                'href' => route('about'),
                'visible' => ($settings['navigation.link.about'] ?? '1') === '1',
            ],
            [
                'key' => 'products',
                'label' => 'Produk',
                'href' => route('products.index'),
                'visible' => ($settings['navigation.link.products'] ?? '1') === '1',
            ],
            [
                'key' => 'gallery',
                'label' => 'Galeri',
                'href' => route('gallery.index'),
                'visible' => ($settings['navigation.link.gallery'] ?? '1') === '1',
            ],
            [
                'key' => 'contact',
                'label' => 'Kontak',
                'href' => route('contact'),
                'visible' => ($settings['navigation.link.contact'] ?? '1') === '1',
            ],
            [
                'key' => 'articles',
                'label' => 'Blog',
                'href' => route('articles.index'),
                'visible' => ($settings['navigation.link.articles'] ?? '1') === '1',
            ],
            [
                'key' => 'article-detail',
                'label' => 'Detail Artikel',
                'href' => $articleDetailUrl ?? '#',
                'visible' => $articleDetailUrl !== null && ($settings['navigation.link.article-detail'] ?? '0') === '1',
            ],
            [
                'key' => 'orders',
                'label' => 'Pesanan Saya',
                'href' => route('orders.index'),
                'visible' => ($settings['navigation.link.orders'] ?? '1') === '1',
            ],
        ];

        $paymentGateway = Setting::getValue('payment.gateway');
        $paymentActive = ! empty($paymentGateway);

        return [
            'brand' => [
                'visible' => $brandVisible,
                'label' => $brandLabel,
                'logo' => $brandLogo,
                'icon' => $brandIcon,
                'url' => url('/'),
            ],
            'links' => $links,
            'show_cart' => $paymentActive && ($settings['navigation.icon.cart'] ?? '1') === '1',
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

    /**
     * Retrieve floating contact button configuration for a theme.
     */
    public static function floatingButtons(string $theme): array
    {
        $settings = self::get($theme);

        $visible = ($settings['floating.visible'] ?? '0') === '1';
        $rawItems = json_decode($settings['floating.buttons'] ?? '[]', true);
        $items = is_array($rawItems) ? $rawItems : [];

        $buttons = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? 'whatsapp';
            $type = in_array($type, ['whatsapp', 'phone'], true) ? $type : 'whatsapp';
            $label = trim((string) ($item['label'] ?? ''));
            $target = trim((string) ($item['target'] ?? ''));
            $message = trim((string) ($item['message'] ?? ''));

            if ($target === '') {
                continue;
            }

            $digits = preg_replace('/\D+/', '', $target);
            if (! is_string($digits) || $digits === '') {
                continue;
            }

            $hasLeadingPlus = str_starts_with($target, '+');
            $displayNumber = $hasLeadingPlus ? '+' . $digits : $digits;
            $telValue = $displayNumber;

            if ($type === 'whatsapp') {
                $href = 'https://wa.me/' . $digits;
                if ($message !== '') {
                    $href .= '?text=' . urlencode($message);
                }
                $external = true;
            } else {
                $href = 'tel:' . $telValue;
                $external = false;
            }

            if ($label === '') {
                $label = $type === 'phone' ? 'Hubungi Kami' : 'Chat WhatsApp';
            }

            $ariaLabelParts = [$label];
            if ($displayNumber !== '') {
                $ariaLabelParts[] = $displayNumber;
            }

            $buttons[] = [
                'type' => $type,
                'label' => $label,
                'href' => $href,
                'external' => $external,
                'number' => $displayNumber,
                'aria_label' => trim(implode(' ', $ariaLabelParts)),
            ];
        }

        if ($buttons === []) {
            $visible = false;
        }

        return [
            'visible' => $visible,
            'buttons' => $buttons,
        ];
    }

    /**
     * Determine the currently selected variation key for a theme.
     */
    public static function variationKey(string $theme): string
    {
        $settings = self::get($theme);
        $selected = $settings['theme.variation'] ?? null;

        return ThemeVariation::ensureKey($theme, $selected);
    }

    /**
     * Retrieve the active variation configuration for a theme.
     */
    public static function variation(string $theme): array
    {
        $key = self::variationKey($theme);

        return ThemeVariation::get($theme, $key);
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
            'theme-istudio' => 'iSTUDIO',
            default => 'Storefront',
        };
    }

    protected static function firstArticleDetailUrl(): ?string
    {
        $articleSettings = PageSetting::forPage('article');
        $items = json_decode($articleSettings['articles.items'] ?? '[]', true);

        if (! is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (! empty($item['slug'])) {
                return route('articles.show', ['slug' => $item['slug']]);
            }
        }

        return null;
    }

    public static function flushCache(?string $theme = null): void
    {
        if ($theme === null) {
            self::$settingsCache = [];

            return;
        }

        unset(self::$settingsCache[$theme]);
    }
}
