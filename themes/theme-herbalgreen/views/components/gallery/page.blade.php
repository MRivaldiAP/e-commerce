@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('gallery');
    $activeSections = \App\Support\PageElements::activeSectionKeys('gallery', $themeName, $settings);
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

    @php
        $showFiltersSection = in_array('filters', $activeSections, true);
        $showGridSection = in_array('grid', $activeSections, true);
        $renderSections = [];
        $listInserted = false;
        foreach ($activeSections as $sectionKey) {
            if (in_array($sectionKey, ['filters', 'grid'], true)) {
                if (! $listInserted && ($showFiltersSection || $showGridSection)) {
                    $renderSections[] = 'list';
                    $listInserted = true;
                }
            } else {
                $renderSections[] = $sectionKey;
            }
        }
        if (! $listInserted && ($showFiltersSection || $showGridSection)) {
            $renderSections[] = 'list';
        }
    @endphp

    @foreach ($renderSections as $sectionKey)
        @switch($sectionKey)
            @case('hero')
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.gallery.sections.hero', [
                    'settings' => $settings,
                    'heroBackground' => $heroBackground,
                ])
                @break

            @case('list')
                @includeWhen($showFiltersSection || $showGridSection, 'themeHerbalGreen::components.gallery.sections.list', [
                    'settings' => $settings,
                    'categoryCollection' => $categoryCollection,
                    'itemCollection' => $itemCollection,
                    'hasUncategorized' => $hasUncategorized,
                    'showFiltersSection' => $showFiltersSection,
                    'showGridSection' => $showGridSection,
                ])
                @break
        @endswitch
    @endforeach

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
