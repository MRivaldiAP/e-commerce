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
        $collectionForMeta = collect($articles ?? []);
        $listOgImage = null;
        $firstImage = $collectionForMeta->first()['image'] ?? null;
        if (!empty($firstImage)) {
            $listOgImage = str_starts_with($firstImage, 'http://') || str_starts_with($firstImage, 'https://')
                ? $firstImage
                : asset('storage/' . ltrim($firstImage, '/'));
        } elseif (!empty($settings['hero.image'] ?? null)) {
            $heroImage = $settings['hero.image'];
            $listOgImage = str_starts_with($heroImage, 'http://') || str_starts_with($heroImage, 'https://')
                ? $heroImage
                : asset('storage/' . ltrim($heroImage, '/'));
        }
    @endphp
    @if($listOgImage)
        <meta property="og:image" content="{{ $listOgImage }}">
    @endif
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-second';
    $settings = $settings ?? [];
    $meta = $meta ?? [];
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
    $emptyText = $settings['list.empty_text'] ?? 'Belum ada artikel untuk ditampilkan.';
    $searchPlaceholder = $settings['search.placeholder'] ?? 'Cari artikel...';

    function article_image_path($path) {
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
<section id="hero" class="breadcrumb-section set-bg {{ ($settings['hero.mask'] ?? '0') == '1' ? 'breadcrumb-section--mask' : '' }}" data-setbg="{{ !empty($settings['hero.image']) ? asset('storage/'.$settings['hero.image']) : asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel') }}</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <span>{{ $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel') }}</span>
                    </div>
                    @if(!empty($settings['hero.description']))
                        <p class="mt-3 text-white-50">{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section id="list" class="blog spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8">
                @forelse($articles as $article)
                <div class="blog__item">
                    <div class="blog__item__pic">
                        @php $image = article_image_path($article['image'] ?? null); @endphp
                        <img src="{{ $image ?? asset('storage/themes/theme-second/img/blog/blog-1.jpg') }}" alt="{{ $article['title'] ?? 'Artikel' }}">
                    </div>
                    <div class="blog__item__text">
                        <ul>
                            @if(!empty($article['author']))
                                <li><i class="fa fa-user"></i> {{ $article['author'] }}</li>
                            @endif
                            @if(!empty($article['date_formatted']))
                                <li><i class="fa fa-calendar-o"></i> {{ $article['date_formatted'] }}</li>
                            @endif
                        </ul>
                        <h5><a href="{{ route('articles.show', ['slug' => $article['slug']]) }}">{{ $article['title'] ?? 'Tanpa Judul' }}</a></h5>
                        @if(!empty($article['excerpt']))
                            <p>{{ $article['excerpt'] }}</p>
                        @endif
                        <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="blog__btn">{{ $buttonLabel }} <span class="arrow_right"></span></a>
                    </div>
                </div>
                @empty
                <div class="alert alert-light border">
                    {{ $emptyText }}
                </div>
                @endforelse
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="blog__sidebar">
                    <div id="search" class="blog__sidebar__search">
                        <form method="GET">
                            <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
                            <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
                            <input type="text" name="search" placeholder="{{ $searchPlaceholder }}" value="{{ $filters['search'] ?? '' }}">
                            <button type="submit"><span class="icon_search"></span></button>
                        </form>
                    </div>
                    @if(!empty($filters['year']) || !empty($filters['month']) || !empty($filters['search']))
                        <div class="blog__sidebar__item">
                            <a href="{{ route('articles.index') }}" class="site-btn w-100 text-center">Reset Filter</a>
                        </div>
                    @endif
                    @if(($settings['timeline.visible'] ?? '1') == '1')
                    <div id="timeline" class="blog__sidebar__item">
                        <h4>{{ $settings['timeline.heading'] ?? 'Arsip Artikel' }}</h4>
                        @if($timeline->isEmpty())
                            <p class="text-muted mb-0">Belum ada arsip.</p>
                        @else
                        <div class="blog__sidebar__item__categories">
                            <ul>
                                @foreach($timeline as $year => $months)
                                    <li>
                                        <span class="d-block fw-bold">{{ $year }}</span>
                                        <ul class="list-unstyled ms-3 mt-2">
                                            @foreach($months as $monthKey => $monthData)
                                                <li class="mb-2">
                                                    <a href="{{ route('articles.index', ['year' => $year, 'month' => $monthKey, 'search' => $filters['search'] ?? null]) }}" class="d-block">
                                                        {{ $monthData['name'] ?? $monthKey }} ({{ $monthData['articles']->count() }})
                                                    </a>
                                                    <ul class="list-unstyled ms-3 mt-1">
                                                        @foreach($monthData['articles'] as $item)
                                                            <li><a href="{{ route('articles.show', ['slug' => $item['slug']]) }}">{{ $item['title'] ?? 'Artikel' }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>

@once
    <style>
        .breadcrumb-section--mask::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
        }
        .blog__sidebar__item__categories > ul > li {
            margin-bottom: 1.5rem;
        }
        .blog__sidebar__item__categories ul ul li {
            font-size: 0.95rem;
        }
    </style>
@endonce
</body>
</html>
