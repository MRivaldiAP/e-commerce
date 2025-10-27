@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('product');
    $activeSections = \App\Support\PageElements::activeSectionKeys($themeName, $settings);
    $query = \App\Models\Product::query()->with(['images', 'promotions']);
    if ($search = request('search')) {
        $query->where('name', 'like', "%{$search}%");
    }
    if ($categorySlug = request('category')) {
        $query->whereHas('categories', fn ($q) => $q->where('slug', $categorySlug));
    }
    if ($sort = request('sort')) {
        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'sold_desc') {
            $query->withCount('orderItems')->orderByDesc('order_items_count');
        }
    }
    $products = $query->paginate(15)->withQueryString();
    $categories = \App\Models\Category::all();
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $cartSummary = \App\Support\Cart::summary();
    $heroImage = \App\Support\ThemeMedia::url($settings['hero.image'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['title'] ?? 'Produk' }}</title>
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
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.product.sections.hero', [
                    'settings' => $settings,
                    'heroImage' => $heroImage,
                ])
                @break
        @endswitch
    @endforeach

    @include('themeHerbalGreen::components.product.sections.list', [
        'products' => $products,
        'categories' => $categories,
    ])

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
