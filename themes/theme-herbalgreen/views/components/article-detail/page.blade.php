@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $detailSettings = $settings ?? \App\Models\PageSetting::forPage('article-detail');
    $activeSections = \App\Support\PageElements::activeSectionKeys($themeName, $detailSettings);
    $listSettings = $listSettings ?? \App\Models\PageSetting::forPage('article');
    $current = $article ?? [];
    $recommended = collect($recommended ?? []);
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $cartSummary = \App\Support\Cart::summary();

    $dateObject = $current['date_object'] ?? null;
    $dateFormatted = $dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null;

    $heroImage = \App\Support\ThemeMedia::url($detailSettings['hero.image'] ?? null)
        ?: 'https://images.unsplash.com/photo-1487611459768-bd414656ea10?auto=format&fit=crop&w=1600&q=80';
    $ogImage = \App\Support\ThemeMedia::url($current['image'] ?? null);

    $seoTitle = $meta['title'] ?? ($current['title'] ?? 'Artikel');
    $seoDescription = $meta['description'] ?? null;

    $showHeroSection = in_array('hero', $activeSections, true);
    $showMetaSection = in_array('meta', $activeSections, true);
    $showCommentsSection = in_array('comments', $activeSections, true);
    $showRecommendationsSection = in_array('recommendations', $activeSections, true);

    $commentsView = '';
    if ($showCommentsSection && ($detailSettings['comments.visible'] ?? '1') == '1') {
        $commentsView = view('themeHerbalGreen::components.article-detail.sections.comments', [
            'settings' => $detailSettings,
        ])->render();
    }

    $recommendationsView = '';
    if ($showRecommendationsSection && ($detailSettings['recommendations.visible'] ?? '1') == '1' && $recommended->isNotEmpty()) {
        $recommendationsView = view('themeHerbalGreen::components.article-detail.sections.recommendations', [
            'settings' => $detailSettings,
            'recommended' => $recommended,
        ])->render();
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>
    @if(!empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
        <meta property="og:description" content="{{ $seoDescription }}">
    @endif
    <meta property="og:title" content="{{ $seoTitle }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <link rel="stylesheet" href="{{ asset('themes/' . $themeName . '/theme.css') }}">
</head>
<body>
    @include('themeHerbalGreen::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @includeWhen($showHeroSection && ($detailSettings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.article-detail.sections.hero', [
        'settings' => $detailSettings,
        'article' => $current,
        'heroImage' => $heroImage,
    ])

    @include('themeHerbalGreen::components.article-detail.sections.content', [
        'article' => $current,
        'settings' => $detailSettings,
        'dateFormatted' => $dateFormatted,
        'commentsView' => $commentsView,
        'recommendationsView' => $recommendationsView,
        'showMetaSection' => $showMetaSection,
        'showCommentsSection' => $showCommentsSection,
        'showRecommendationsSection' => $showRecommendationsSection,
    ])

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])

    @once
        <style>
            .page-hero {
                position: relative;
                background-size: cover;
                background-position: center;
                min-height: 40vh;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: #fff;
            }
            .page-hero .overlay {
                position: absolute;
                inset: 0;
                background: rgba(255,255,255,0.45);
            }
            .page-hero .overlay.overlay-dark {
                background: rgba(0,0,0,0.5);
            }
            .page-hero__content {
                position: relative;
                max-width: 640px;
            }
            .page-hero__content .tagline {
                letter-spacing: 2px;
                text-transform: uppercase;
                font-weight: 600;
                color: #fff;
            }
            .page-hero__content h1 {
                margin: 0.5rem 0 0;
                color: #fff;
            }
            .article-detail {
                display: grid;
                grid-template-columns: minmax(0, 3fr) minmax(260px, 1fr);
                gap: 2.5rem;
                padding: 4rem 2rem;
            }
            @media (max-width: 992px) {
                .article-detail {
                    grid-template-columns: 1fr;
                }
            }
            .article-detail__main {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }
            .article-detail__media {
                padding-top: 56%;
                background-size: cover;
                background-position: center;
            }
            .article-detail__body {
                padding: 2.5rem;
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            .article-detail__meta {
                font-size: 0.9rem;
                color: rgba(27, 94, 32, 0.7);
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
            }
            .article-detail__content p {
                line-height: 1.7;
                margin-bottom: 1.5rem;
            }
            .article-detail__comments {
                padding: 2.5rem;
                border-top: 1px solid rgba(46, 125, 50, 0.1);
            }
            .article-detail__comments h2 {
                margin-top: 0;
            }
            .article-detail__comments .text-muted {
                color: rgba(27, 94, 32, 0.6);
            }
            .article-detail__notice {
                background: rgba(46, 125, 50, 0.1);
                padding: 1rem 1.25rem;
                border-radius: 8px;
                color: rgba(27, 94, 32, 0.8);
            }
            .article-detail__sidebar {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            .sidebar-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                padding: 1.75rem;
            }
            .sidebar-card h3 {
                margin-top: 0;
            }
            .sidebar-list {
                list-style: none;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .sidebar-list li a {
                display: flex;
                flex-direction: column;
                text-decoration: none;
                color: var(--color-text);
                gap: 0.25rem;
            }
            .sidebar-list li a:hover .title {
                color: var(--color-primary);
            }
            .sidebar-list .date {
                font-size: 0.85rem;
                color: rgba(27, 94, 32, 0.6);
            }
        </style>
    @endonce

    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
