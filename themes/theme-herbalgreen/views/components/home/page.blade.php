@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('home', $themeName);
    $activeSections = \App\Support\PageElements::activeSectionKeys('home', $themeName, $settings);
    $products = \App\Models\Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    if (! is_array($testimonials)) {
        $testimonials = [];
    }
    $services = json_decode($settings['services.items'] ?? '[]', true);
    if (! is_array($services)) {
        $services = [];
    }
    $cartSummary = \App\Support\Cart::summary();
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $heroImage = \App\Support\ThemeMedia::url($settings['hero.image'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herbal Green</title>
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

    @foreach ($activeSections as $sectionKey)
        @switch($sectionKey)
            @case('hero')
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.home.sections.hero', [
                    'settings' => $settings,
                    'heroImage' => $heroImage,
                ])
                @break

            @case('about')
                @includeWhen(($settings['about.visible'] ?? '1') == '1', 'themeHerbalGreen::components.home.sections.about', [
                    'settings' => $settings,
                ])
                @break

            @case('products')
                @includeWhen(($settings['products.visible'] ?? '1') == '1', 'themeHerbalGreen::components.home.sections.products', [
                    'settings' => $settings,
                    'products' => $products,
                ])
                @break

            @case('services')
                @includeWhen(($settings['services.visible'] ?? '1') == '1' && count($services), 'themeHerbalGreen::components.home.sections.services', [
                    'settings' => $settings,
                    'services' => $services,
                ])
                @break

            @case('testimonials')
                @includeWhen(($settings['testimonials.visible'] ?? '1') == '1' && count($testimonials), 'themeHerbalGreen::components.home.sections.testimonials', [
                    'testimonials' => $testimonials,
                ])
                @break

            @case('contact')
                @includeWhen(($settings['contact.visible'] ?? '1') == '1', 'themeHerbalGreen::components.home.sections.contact', [
                    'settings' => $settings,
                ])
                @break
        @endswitch
    @endforeach

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
