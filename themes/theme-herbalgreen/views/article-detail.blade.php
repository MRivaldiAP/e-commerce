<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}">
    @php
        $ogImage = null;
        if (!empty($article['image'] ?? null)) {
            $path = $article['image'];
            $ogImage = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
                ? $path
                : asset('storage/' . ltrim($path, '/'));
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
    $detailSettings = $settings ?? PageSetting::forPage('article-detail');
    $listSettings = $listSettings ?? PageSetting::forPage('article');
    $current = $article ?? [];
    $recommended = collect($recommended ?? []);

    $dateObject = $current['date_object'] ?? null;
    $dateFormatted = $dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null;

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

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

@if(($detailSettings['hero.visible'] ?? '1') == '1')
<section id="hero" class="page-hero" style="background-image:url('{{ !empty($detailSettings['hero.image']) ? asset('storage/'.$detailSettings['hero.image']) : 'https://images.unsplash.com/photo-1487611459768-bd414656ea10?auto=format&fit=crop&w=1600&q=80' }}')">
    <div class="overlay{{ ($detailSettings['hero.mask'] ?? '0') == '1' ? ' overlay-dark' : '' }}"></div>
    <div class="page-hero__content">
        <span class="tagline">{{ $detailSettings['hero.title'] ?? 'Artikel' }}</span>
        <h1>{{ $current['title'] ?? 'Artikel' }}</h1>
    </div>
</section>
@endif

<section id="content" class="article-detail">
    <article class="article-detail__main">
        @php $image = hg_article_image($current['image'] ?? null); @endphp
        @if($image)
            <div class="article-detail__media" style="background-image:url('{{ $image }}')"></div>
        @endif
        <div class="article-detail__body">
            <div class="article-detail__meta">
                @if(($detailSettings['meta.show_date'] ?? '1') == '1' && $dateFormatted)
                    <span>{{ $dateFormatted }}</span>
                @endif
                @if(($detailSettings['meta.show_author'] ?? '1') == '1' && !empty($current['author']))
                    <span>• {{ $current['author'] }}</span>
                @endif
            </div>
            <h1>{{ $current['title'] ?? 'Artikel' }}</h1>
            <div class="article-detail__content">
                {!! $current['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
            </div>
        </div>
        <div id="comments" class="article-detail__comments">
            @if(($detailSettings['comments.visible'] ?? '1') == '1')
                <h2>{{ $detailSettings['comments.heading'] ?? 'Komentar' }}</h2>
                <p class="text-muted">Fitur komentar akan tersedia segera.</p>
            @else
                <div class="article-detail__notice">{{ $detailSettings['comments.disabled_text'] ?? 'Komentar dimatikan.' }}</div>
            @endif
        </div>
    </article>
    <aside id="recommendations" class="article-detail__sidebar">
        @if(($detailSettings['recommendations.visible'] ?? '1') == '1' && $recommended->isNotEmpty())
            <div class="sidebar-card">
                <h3>{{ $detailSettings['recommendations.heading'] ?? 'Artikel Lainnya' }}</h3>
                <ul class="sidebar-list">
                    @foreach($recommended as $item)
                        <li>
                            <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}">
                                <span class="title">{{ $item['title'] ?? 'Artikel' }}</span>
                                <span class="date">{{ $item['date_formatted'] ?? '' }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
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

{!! view()->file(base_path('themes/' . $theme . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $theme,
])->render() !!}
</body>
</html>
