<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? ($settings['hero.heading'] ?? 'Artikel') }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($settings['hero.heading'] ?? 'Artikel') }}">
    @php
        $ogImage = null;
        $heroImage = $settings['hero.image'] ?? null;
        if (!empty($heroImage)) {
            $ogImage = str_starts_with($heroImage, 'http://') || str_starts_with($heroImage, 'https://')
                ? $heroImage
                : asset('storage/' . ltrim($heroImage, '/'));
        } elseif (!empty($articles[0]['image'] ?? null)) {
            $articleImage = $articles[0]['image'];
            $ogImage = str_starts_with($articleImage, 'http://') || str_starts_with($articleImage, 'https://')
                ? $articleImage
                : asset('storage/' . ltrim($articleImage, '/'));
        }
    @endphp
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = $settings ?? PageSetting::forPage('article');
    $articles = collect($articles ?? [])->filter(fn ($item) => !empty($item['slug'] ?? null));
    $timeline = collect($timeline ?? []);
    $filters = $filters ?? [
        'search' => request('search'),
        'year' => request('year'),
        'month' => request('month'),
    ];

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $pageTitle = $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel');
    $buttonLabel = $settings['list.button_label'] ?? 'Baca Selengkapnya';
    $emptyText = $settings['list.empty_text'] ?? 'Belum ada artikel.';
    $searchPlaceholder = $settings['search.placeholder'] ?? 'Cari artikel...';

    function hg_article_image($path) {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="page-hero" style="background-image:url('{{ !empty($settings['hero.image']) ? asset('storage/'.$settings['hero.image']) : 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80' }}')">
    <div class="overlay{{ ($settings['hero.mask'] ?? '0') == '1' ? ' overlay-dark' : '' }}"></div>
    <div class="page-hero__content">
        <span class="tagline">{{ $settings['hero.description'] ?? 'Kabar dan cerita terbaru' }}</span>
        <h1>{{ $settings['hero.heading'] ?? 'Artikel' }}</h1>
    </div>
</section>
@endif

<section id="list" class="article-layout">
    <div class="article-layout__content">
        @forelse($articles as $article)
            <article class="article-card">
                @php $image = hg_article_image($article['image'] ?? null); @endphp
                @if($image)
                    <div class="article-card__media" style="background-image:url('{{ $image }}')"></div>
                @endif
                <div class="article-card__body">
                    <div class="article-card__meta">
                        @if(!empty($article['date_formatted']))
                            <span>{{ $article['date_formatted'] }}</span>
                        @endif
                        @if(!empty($article['author']))
                            <span>• {{ $article['author'] }}</span>
                        @endif
                    </div>
                    <h2>{{ $article['title'] ?? 'Artikel' }}</h2>
                    @if(!empty($article['excerpt']))
                        <p>{{ $article['excerpt'] }}</p>
                    @endif
                    <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="article-card__link">{{ $buttonLabel }}</a>
                </div>
            </article>
        @empty
            <div class="article-empty">{{ $emptyText }}</div>
        @endforelse
    </div>
    <aside class="article-layout__sidebar">
        <div id="search" class="sidebar-card">
            <h3>Cari Artikel</h3>
            <form method="GET" class="sidebar-search">
                <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
                <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
                <input type="text" name="search" placeholder="{{ $searchPlaceholder }}" value="{{ $filters['search'] ?? '' }}">
                <button type="submit">Cari</button>
            </form>
            @if(!empty($filters['search']) || !empty($filters['year']) || !empty($filters['month']))
                <a href="{{ route('articles.index') }}" class="sidebar-reset">Reset Filter</a>
            @endif
        </div>
        @if(($settings['timeline.visible'] ?? '1') == '1')
        <div id="timeline" class="sidebar-card">
            <h3>{{ $settings['timeline.heading'] ?? 'Arsip' }}</h3>
            @if($timeline->isEmpty())
                <p class="text-muted">Belum ada arsip.</p>
            @else
                <div class="timeline">
                    @foreach($timeline as $year => $months)
                        <div class="timeline__year">
                            <h4>{{ $year }}</h4>
                            <ul>
                                @foreach($months as $monthKey => $monthData)
                                    <li>
                                        <a href="{{ route('articles.index', ['year' => $year, 'month' => $monthKey, 'search' => $filters['search'] ?? null]) }}">{{ $monthData['name'] ?? $monthKey }} ({{ $monthData['articles']->count() }})</a>
                                        <ul>
                                            @foreach($monthData['articles'] as $item)
                                                <li><a href="{{ route('articles.show', ['slug' => $item['slug']]) }}">{{ $item['title'] ?? 'Artikel' }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif
    </aside>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

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
</body>
</html>
