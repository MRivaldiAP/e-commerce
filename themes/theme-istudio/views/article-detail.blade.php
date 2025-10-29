<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}">
    @php
        use App\Support\ThemeMedia;

        $detailSettings = $settings ?? [];
        $heroImageForMeta = $detailSettings['hero.image'] ?? null;
        $detailOgImage = null;
        $primaryImage = $article['image'] ?? null;
        if (! empty($primaryImage)) {
            $detailOgImage = str_starts_with($primaryImage, 'http://') || str_starts_with($primaryImage, 'https://')
                ? $primaryImage
                : asset('storage/' . ltrim($primaryImage, '/'));
        } elseif (! empty($heroImageForMeta)) {
            $detailOgImage = ThemeMedia::url($heroImageForMeta);
        }
    @endphp
    @if($detailOgImage)
        <meta property="og:image" content="{{ $detailOgImage }}">
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
    $detailSettings = $settings ?? [];
    $listSettings = $listSettings ?? [];
    $article = $article ?? [];
    $recommended = collect($recommended ?? []);
    $meta = $meta ?? [];

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = $cartSummary ?? Cart::summary();

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

    $heroBackground = $resolveMedia($detailSettings['hero.image'] ?? null, $assetBase('img/hero-slider-2.jpg'));

    $dateObject = $article['date_object'] ?? null;
    if (! $dateObject && ! empty($article['date'])) {
        try {
            $dateObject = \Illuminate\Support\Carbon::parse($article['date']);
        } catch (\Exception $e) {
            $dateObject = null;
        }
    }
    $dateFormatted = $article['date_formatted'] ?? ($dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null);
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

@if(($detailSettings['hero.visible'] ?? '1') === '1')
    <div id="hero" class="container-fluid pb-5 bg-primary hero-header" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container py-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-7 text-center text-lg-start">
                    <h1 class="display-3 mb-0 animated slideInLeft">{{ $article['title'] ?? ($detailSettings['hero.title'] ?? 'Artikel') }}</h1>
                </div>
                <div class="col-lg-5 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ route('articles.index') }}">Artikel</a></li>
                            <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $detailSettings['hero.title'] ?? ($article['title'] ?? 'Detail') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endif

<section id="content" class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-8">
                <article class="card border-0 shadow-sm overflow-hidden">
                    @php
                        $articleImage = $resolveMedia($article['image'] ?? null, null);
                    @endphp
                    @if($articleImage)
                        <img src="{{ $articleImage }}" class="card-img-top" alt="{{ $article['title'] ?? 'Artikel' }}">
                    @endif
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center text-muted small mb-3 gap-3">
                            @if(($detailSettings['meta.show_date'] ?? '1') === '1' && $dateFormatted)
                                <span><i class="far fa-calendar-alt me-2 text-primary"></i>{{ $dateFormatted }}</span>
                            @endif
                            @if(($detailSettings['meta.show_author'] ?? '1') === '1' && ! empty($article['author']))
                                <span><i class="far fa-user me-2 text-primary"></i>{{ $article['author'] }}</span>
                            @endif
                        </div>
                        <h1 class="h3 mb-4">{{ $article['title'] ?? 'Artikel' }}</h1>
                        <div class="article-content">
                            {!! $article['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
                        </div>
                    </div>
                </article>
                <section id="comments" class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        @if(($detailSettings['comments.visible'] ?? '1') === '1')
                            <h3 class="h5 mb-3 text-uppercase">{{ $detailSettings['comments.heading'] ?? 'Komentar' }}</h3>
                            <p class="text-muted mb-0">Fitur komentar akan segera tersedia.</p>
                        @else
                            <div class="alert alert-light border mb-0">{{ $detailSettings['comments.disabled_text'] ?? 'Komentar dinonaktifkan.' }}</div>
                        @endif
                    </div>
                </section>
            </div>
            <div id="recommendations" class="col-lg-4">
                @if(($detailSettings['recommendations.visible'] ?? '1') === '1' && $recommended->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 text-uppercase mb-3">{{ $detailSettings['recommendations.heading'] ?? 'Artikel Lainnya' }}</h3>
                        <ul class="list-unstyled mb-0">
                            @foreach($recommended as $item)
                                <li class="mb-3 pb-3 border-bottom">
                                    <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" class="text-decoration-none">
                                        <span class="d-block fw-semibold text-dark">{{ $item['title'] ?? 'Artikel' }}</span>
                                        @if(!empty($item['date_formatted']))
                                            <small class="text-muted"><i class="far fa-calendar-alt me-2 text-primary"></i>{{ $item['date_formatted'] }}</small>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
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
