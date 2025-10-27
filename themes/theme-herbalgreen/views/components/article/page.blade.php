@php
    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = $settings ?? \App\Models\PageSetting::forPage('article');
    $activeSections = \App\Support\PageElements::activeSectionKeys('article', $themeName, $settings);
    $articles = collect($articles ?? [])->filter(fn ($item) => !empty($item['slug'] ?? null));
    $timeline = collect($timeline ?? []);
    $filters = $filters ?? [
        'search' => request('search'),
        'year' => request('year'),
        'month' => request('month'),
    ];
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $cartSummary = \App\Support\Cart::summary();

    $pageTitle = $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel');
    $buttonLabel = $settings['list.button_label'] ?? 'Baca Selengkapnya';
    $emptyText = $settings['list.empty_text'] ?? 'Belum ada artikel.';
    $searchPlaceholder = $settings['search.placeholder'] ?? 'Cari artikel...';

    $heroImage = \App\Support\ThemeMedia::url($settings['hero.image'] ?? null)
        ?: \App\Support\ThemeMedia::url($articles->first()['image'] ?? null)
        ?: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80';
    $ogImage = \App\Support\ThemeMedia::url($settings['hero.image'] ?? null)
        ?: \App\Support\ThemeMedia::url($articles->first()['image'] ?? null);

    $seoTitle = $meta['title'] ?? $pageTitle;
    $seoDescription = $meta['description'] ?? null;

    $articleImage = function (?string $path) {
        return \App\Support\ThemeMedia::url($path);
    };
    $timelineActive = in_array('timeline', $activeSections, true);
    $renderSections = array_values(array_filter($activeSections, function ($section) {
        return $section !== 'timeline';
    }));
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

    @foreach ($renderSections as $sectionKey)
        @switch($sectionKey)
            @case('hero')
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.article.sections.hero', [
                    'settings' => $settings,
                    'heroImage' => $heroImage,
                ])
                @break

            @case('list')
                @include('themeHerbalGreen::components.article.sections.list', [
                    'articles' => $articles,
                    'buttonLabel' => $buttonLabel,
                    'emptyText' => $emptyText,
                    'filters' => $filters,
                    'searchPlaceholder' => $searchPlaceholder,
                    'timelineVisible' => ($settings['timeline.visible'] ?? '1') == '1' && $timelineActive,
                    'timelineHeading' => $settings['timeline.heading'] ?? 'Arsip',
                    'timeline' => $timeline,
                    'articleImage' => $articleImage,
                ])
                @break
        @endswitch
    @endforeach

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])

    @once
        <style>
            .page-hero {
                position: relative;
                background-size: cover;
                background-position: center;
                min-height: 45vh;
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
                background: rgba(0,0,0,0.45);
            }
            .page-hero__content {
                position: relative;
                max-width: 600px;
            }
            .page-hero__content h1 {
                font-size: 2.5rem;
                margin: 0.5rem 0 0;
                color: #fff;
            }
            .page-hero__content .tagline {
                letter-spacing: 2px;
                text-transform: uppercase;
                font-weight: 600;
            }
            .article-layout {
                display: grid;
                grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
                gap: 2.5rem;
                padding: 4rem 2rem;
            }
            @media (max-width: 992px) {
                .article-layout {
                    grid-template-columns: 1fr;
                }
            }
            .article-layout__content {
                display: grid;
                gap: 2rem;
            }
            .article-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.08);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }
            .article-card__media {
                padding-top: 56%;
                background-size: cover;
                background-position: center;
            }
            .article-card__body {
                padding: 2rem;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .article-card__meta {
                font-size: 0.9rem;
                color: rgba(27, 94, 32, 0.7);
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            .article-card h2 {
                margin: 0;
                font-size: 1.5rem;
                color: var(--color-text);
            }
            .article-card__link {
                align-self: flex-start;
                text-decoration: none;
                font-weight: 600;
                color: var(--color-primary);
            }
            .article-card__link:hover {
                color: var(--color-accent);
            }
            .article-empty {
                background: #fff;
                border-radius: 12px;
                padding: 2rem;
                text-align: center;
                color: rgba(27, 94, 32, 0.7);
            }
            .article-layout__sidebar {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            .sidebar-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.08);
                padding: 1.5rem;
            }
            .sidebar-card h3 {
                margin-top: 0;
            }
            .sidebar-search {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            .sidebar-search input {
                padding: 0.75rem 1rem;
                border: 1px solid rgba(46, 125, 50, 0.2);
                border-radius: 8px;
            }
            .sidebar-search button {
                padding: 0.75rem 1rem;
                border: none;
                border-radius: 8px;
                background: var(--color-primary);
                color: #fff;
                cursor: pointer;
            }
            .sidebar-search button:hover {
                background: var(--color-accent);
            }
            .sidebar-reset {
                display: inline-block;
                margin-top: 0.75rem;
                font-size: 0.9rem;
                color: var(--color-primary);
                text-decoration: none;
            }
            .sidebar-reset:hover {
                color: var(--color-accent);
            }
            .timeline ul {
                list-style: none;
                margin: 0;
                padding-left: 1rem;
            }
            .timeline__year {
                margin-bottom: 1.5rem;
            }
            .timeline__year h4 {
                margin-bottom: 0.5rem;
            }
            .timeline__year > ul > li {
                margin-bottom: 0.75rem;
            }
            .timeline__year a {
                color: var(--color-text);
                text-decoration: none;
            }
            .timeline__year a:hover {
                color: var(--color-primary);
            }
            .timeline__year ul ul {
                padding-left: 1rem;
                margin-top: 0.5rem;
            }
        </style>
    @endonce

    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
