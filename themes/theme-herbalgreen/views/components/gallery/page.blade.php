@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('gallery');
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $cartSummary = \App\Support\Cart::summary();
    $categoryCollection = collect($categories ?? \App\Models\GalleryCategory::orderBy('name')->get());
    $itemCollection = collect($items ?? \App\Models\GalleryItem::with('category')
        ->orderByRaw('position IS NULL')
        ->orderBy('position')
        ->orderBy('created_at', 'desc')
        ->get());
    $hasUncategorized = $itemCollection->contains(fn ($item) => $item->category === null);
    $heroBackground = \App\Support\ThemeMedia::url($settings['hero.background'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $themeName . '/theme.css') }}">
    <script src="{{ asset('themes/' . $themeName . '/theme.js') }}" defer></script>
</head>
<body>
    @include('themeHerbalGreen::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.gallery.sections.hero', [
        'settings' => $settings,
        'heroBackground' => $heroBackground,
    ])

    @include('themeHerbalGreen::components.gallery.sections.list', [
        'settings' => $settings,
        'categoryCollection' => $categoryCollection,
        'itemCollection' => $itemCollection,
        'hasUncategorized' => $hasUncategorized,
    ])

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
