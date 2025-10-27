@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('product-detail');
    $activeSections = \App\Support\PageElements::activeSectionKeys($themeName, $settings);
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $cartSummary = \App\Support\Cart::summary();

    $images = $product->images ?? collect();
    $primaryImage = optional($images->first())->path;
    $imageSources = $images->pluck('path')->filter()->map(fn ($path) => \App\Support\ThemeMedia::url($path))->values();
    if ($imageSources->isEmpty()) {
        $imageSources = collect(['https://via.placeholder.com/600x400?text=No+Image']);
        $primaryImage = null;
    }
    $mainImageUrl = $primaryImage ? \App\Support\ThemeMedia::url($primaryImage) : $imageSources->first();

    $comments = $product->comments ?? collect();

    $recommendationsQuery = \App\Models\Product::query()->where('id', '!=', $product->id);
    if ($product->categories && $product->categories->count()) {
        $recommendationsQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')));
    }
    $recommendations = $recommendationsQuery->with(['images', 'promotions'])->take(5)->get();
    if ($recommendations->count() < 5) {
        $fallback = \App\Models\Product::where('id', '!=', $product->id)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['images', 'promotions'])
            ->take(5 - $recommendations->count())
            ->get();
        $recommendations = $recommendations->concat($fallback);
    }

    $productPromotion = $product->currentPromotion();
    $productHasPromo = $productPromotion && $product->promo_price !== null && $product->promo_price < $product->price;
    $productFinalPrice = $product->final_price;
    $heroImage = \App\Support\ThemeMedia::url($settings['hero.image'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk</title>
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

    @php($detailsRendered = false)
    @foreach ($activeSections as $sectionKey)
        @switch($sectionKey)
            @case('hero')
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.product-detail.sections.hero', [
                    'settings' => $settings,
                    'product' => $product,
                    'heroImage' => $heroImage,
                ])
                @include('themeHerbalGreen::components.product-detail.sections.details', [
                    'product' => $product,
                    'productHasPromo' => $productHasPromo,
                    'productPromotion' => $productPromotion,
                    'productFinalPrice' => $productFinalPrice,
                    'mainImageUrl' => $mainImageUrl,
                    'imageSources' => $imageSources,
                ])
                @php($detailsRendered = true)
                @break

            @case('comments')
                @if (! $detailsRendered)
                    @include('themeHerbalGreen::components.product-detail.sections.details', [
                        'product' => $product,
                        'productHasPromo' => $productHasPromo,
                        'productPromotion' => $productPromotion,
                        'productFinalPrice' => $productFinalPrice,
                        'mainImageUrl' => $mainImageUrl,
                        'imageSources' => $imageSources,
                    ])
                    @php($detailsRendered = true)
                @endif
                @includeWhen(($settings['comments.visible'] ?? '1') == '1', 'themeHerbalGreen::components.product-detail.sections.comments', [
                    'settings' => $settings,
                    'comments' => $comments,
                ])
                @break

            @case('recommendations')
                @if (! $detailsRendered)
                    @include('themeHerbalGreen::components.product-detail.sections.details', [
                        'product' => $product,
                        'productHasPromo' => $productHasPromo,
                        'productPromotion' => $productPromotion,
                        'productFinalPrice' => $productFinalPrice,
                        'mainImageUrl' => $mainImageUrl,
                        'imageSources' => $imageSources,
                    ])
                    @php($detailsRendered = true)
                @endif
                @includeWhen(($settings['recommendations.visible'] ?? '1') == '1' && $recommendations->count(), 'themeHerbalGreen::components.product-detail.sections.recommendations', [
                    'settings' => $settings,
                    'recommendations' => $recommendations,
                ])
                @break
        @endswitch
    @endforeach

    @if (! $detailsRendered)
        @include('themeHerbalGreen::components.product-detail.sections.details', [
            'product' => $product,
            'productHasPromo' => $productHasPromo,
            'productPromotion' => $productPromotion,
            'productFinalPrice' => $productFinalPrice,
            'mainImageUrl' => $mainImageUrl,
            'imageSources' => $imageSources,
        ])
    @endif

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
