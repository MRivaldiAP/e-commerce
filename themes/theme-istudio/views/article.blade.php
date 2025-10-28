<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? ($settings['hero.heading'] ?? 'Artikel') }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($settings['hero.heading'] ?? 'Artikel') }}">
    @php
        use App\Support\ThemeMedia;

        $articlesForMeta = collect($articles ?? []);
        $heroImageForMeta = $settings['hero.image'] ?? null;
        $listOgImage = null;
        $firstImage = $articlesForMeta->first()['image'] ?? null;
        if (! empty($firstImage)) {
            $listOgImage = str_starts_with($firstImage, 'http://') || str_starts_with($firstImage, 'https://')
                ? $firstImage
                : asset('storage/' . ltrim($firstImage, '/'));
        } elseif (! empty($heroImageForMeta)) {
            $listOgImage = ThemeMedia::url($heroImageForMeta);
        }
    @endphp
    @if($listOgImage)
        <meta property="og:image" content="{{ $listOgImage }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/css/style.css') }}" rel="stylesheet">
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-istudio';
    $settings = $settings ?? [];
    $meta = $meta ?? [];
    $articles = collect($articles ?? [])->filter(fn ($item) => ! empty($item['slug'] ?? null));
    $timeline = collect($timeline ?? []);
    $filters = $filters ?? [
        'search' => request('search'),
        'year' => request('year'),
        'month' => request('month'),
    ];

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = $cartSummary ?? Cart::summary();

    $pageTitle = $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel');
    $buttonLabel = $settings['list.button_label'] ?? 'Baca Selengkapnya';
    $emptyText = $settings['list.empty_text'] ?? 'Belum ada artikel tersedia.';
    $searchPlaceholder = $settings['search.placeholder'] ?? 'Cari artikel...';

    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $heroBackground = $resolveMedia($settings['hero.image'] ?? null, $assetBase('img/hero-slider-1.jpg'));
@endphp
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($settings['hero.visible'] ?? '1') === '1')
    <div id="hero" class="container-fluid pb-5 bg-primary hero-header" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container py-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <h1 class="display-1 mb-0 animated slideInLeft">{{ $pageTitle }}</h1>
                </div>
                <div class="col-lg-6 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $pageTitle }}</li>
                        </ol>
                    </nav>
                    @if(!empty($settings['hero.description']))
                        <p class="text-muted mt-3 mb-0">{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<section id="list" class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-8">
                @forelse($articles as $article)
                <article class="card border-0 shadow-sm mb-4 overflow-hidden">
                    @php
                        $articleImage = $resolveMedia($article['image'] ?? null, $assetBase('img/project-1.jpg'));
                    @endphp
                    @if($articleImage)
                        <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="d-block">
                            <img src="{{ $articleImage }}" class="card-img-top" alt="{{ $article['title'] ?? 'Artikel' }}">
                        </a>
                    @endif
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center text-muted small mb-3 gap-3">
                            @if(!empty($article['date_formatted']))
                                <span><i class="far fa-calendar-alt me-2 text-primary"></i>{{ $article['date_formatted'] }}</span>
                            @endif
                            @if(!empty($article['author']))
                                <span><i class="far fa-user me-2 text-primary"></i>{{ $article['author'] }}</span>
                            @endif
                        </div>
                        <h2 class="h4 mb-3"><a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="text-decoration-none text-dark">{{ $article['title'] ?? 'Artikel' }}</a></h2>
                        @if(!empty($article['excerpt']))
                            <p class="mb-4 text-muted">{{ $article['excerpt'] }}</p>
                        @endif
                        <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="btn btn-primary px-4 py-2">{{ $buttonLabel }}</a>
                    </div>
                </article>
                @empty
                <div class="alert alert-light border">{{ $emptyText }}</div>
                @endforelse
            </div>
            <div class="col-lg-4">
                <div id="search" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3 text-uppercase">Cari Artikel</h3>
                        <form method="GET" class="d-flex flex-column gap-3">
                            <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
                            <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
                            <input type="text" name="search" class="form-control" placeholder="{{ $searchPlaceholder }}" value="{{ $filters['search'] ?? '' }}">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </form>
                        @if(!empty($filters['search']) || !empty($filters['year']) || !empty($filters['month']))
                            <a href="{{ route('articles.index') }}" class="btn btn-link px-0 mt-3">Reset Filter</a>
                        @endif
                    </div>
                </div>
                @if(($settings['timeline.visible'] ?? '1') === '1')
                <div id="timeline" class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3 text-uppercase">{{ $settings['timeline.heading'] ?? 'Arsip' }}</h3>
                        @if($timeline->isEmpty())
                            <p class="text-muted mb-0">Belum ada arsip.</p>
                        @else
                            <div class="accordion" id="timelineAccordion">
                                @foreach($timeline as $year => $months)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $year }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $year }}" aria-expanded="false" aria-controls="collapse-{{ $year }}">
                                                {{ $year }}
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $year }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $year }}" data-bs-parent="#timelineAccordion">
                                            <div class="accordion-body">
                                                @foreach($months as $monthKey => $monthData)
                                                    <div class="mb-3">
                                                        <a href="{{ route('articles.index', ['year' => $year, 'month' => $monthKey, 'search' => $filters['search'] ?? null]) }}" class="fw-semibold d-block mb-2 text-decoration-none text-dark">
                                                            <i class="far fa-calendar-alt me-2 text-primary"></i>{{ $monthData['name'] ?? $monthKey }} ({{ $monthData['articles']->count() }})
                                                        </a>
                                                        <ul class="list-unstyled ps-3 mb-0">
                                                            @foreach($monthData['articles'] as $item)
                                                                <li class="mb-1">
                                                                    <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" class="text-decoration-none text-muted">
                                                                        <i class="far fa-file-alt me-2 text-primary"></i>{{ $item['title'] ?? 'Artikel' }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
    'brand' => $navigation['brand'],
])->render() !!}

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ $assetBase('lib/wow/wow.min.js') }}"></script>
<script src="{{ $assetBase('lib/easing/easing.min.js') }}"></script>
<script src="{{ $assetBase('lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ $assetBase('lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ $assetBase('js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
