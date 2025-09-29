<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}">
    @php
        $detailOgImage = null;
        $primaryImage = $article['image'] ?? null;
        if (!empty($primaryImage)) {
            $detailOgImage = str_starts_with($primaryImage, 'http://') || str_starts_with($primaryImage, 'https://')
                ? $primaryImage
                : asset('storage/' . ltrim($primaryImage, '/'));
        } elseif (!empty($settings['hero.image'] ?? null)) {
            $heroImage = $settings['hero.image'];
            $detailOgImage = str_starts_with($heroImage, 'http://') || str_starts_with($heroImage, 'https://')
                ? $heroImage
                : asset('storage/' . ltrim($heroImage, '/'));
        }
    @endphp
    @if($detailOgImage)
        <meta property="og:image" content="{{ $detailOgImage }}">
    @endif
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $themeName = $theme ?? 'theme-restoran';
    $detailSettings = $settings ?? [];
    $listSettings = $listSettings ?? [];
    $article = $article ?? [];
    $recommended = collect($recommended ?? []);
    $meta = $meta ?? [];

    $dateObject = $article['date_object'] ?? null;
    if (! $dateObject && !empty($article['date'])) {
        try {
            $dateObject = \\Illuminate\\Support\\Carbon::parse($article['date']);
        } catch (\Exception $e) {
            $dateObject = null;
        }
    }
    $dateFormatted = $article['date_formatted'] ?? ($dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null);

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $heroMaskEnabled = ($detailSettings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($detailSettings['hero.image'] ?? null) ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";

    function restoran_article_image($path) {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }
@endphp
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    @if(($detailSettings['hero.visible'] ?? '1') == '1')
    <div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $article['title'] ?? ($detailSettings['hero.title'] ?? 'Artikel') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('articles.index') }}">Artikel</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">{{ $detailSettings['hero.title'] ?? ($article['title'] ?? 'Detail') }}</li>
                </ol>
            </nav>
        </div>
    </div>
    @endif
</div>

<div id="content" class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            <article class="card border-0 shadow-sm">
                @php $image = restoran_article_image($article['image'] ?? null); @endphp
                @if($image)
                    <img src="{{ $image }}" class="card-img-top" alt="{{ $article['title'] ?? 'Artikel' }}">
                @endif
                <div class="card-body p-4">
                    <div class="d-flex align-items-center text-muted mb-3 small">
                        @if(($detailSettings['meta.show_date'] ?? '1') == '1' && $dateFormatted)
                            <span class="me-3"><i class="far fa-calendar-alt me-1"></i>{{ $dateFormatted }}</span>
                        @endif
                        @if(($detailSettings['meta.show_author'] ?? '1') == '1' && !empty($article['author']))
                            <span><i class="far fa-user me-1"></i>{{ $article['author'] }}</span>
                        @endif
                    </div>
                    <h1 class="mb-4 h3">{{ $article['title'] ?? 'Artikel' }}</h1>
                    <div class="article-content">
                        {!! $article['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
                    </div>
                </div>
            </article>
            <section id="comments" class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    @if(($detailSettings['comments.visible'] ?? '1') == '1')
                        <h4 class="mb-3">{{ $detailSettings['comments.heading'] ?? 'Komentar' }}</h4>
                        <p class="text-muted mb-0">Fitur komentar akan segera tersedia.</p>
                    @else
                        <div class="alert alert-light border mb-0">{{ $detailSettings['comments.disabled_text'] ?? 'Komentar dinonaktifkan.' }}</div>
                    @endif
                </div>
            </section>
        </div>
        <div id="recommendations" class="col-lg-4">
            @if(($detailSettings['recommendations.visible'] ?? '1') == '1' && $recommended->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">{{ $detailSettings['recommendations.heading'] ?? 'Artikel Lainnya' }}</h4>
                    <ul class="list-unstyled mb-0">
                        @foreach($recommended as $item)
                            <li class="mb-3">
                                <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" class="text-decoration-none">
                                    <span class="d-block fw-semibold">{{ $item['title'] ?? 'Artikel' }}</span>
                                    <small class="text-muted">{{ $item['date_formatted'] ?? '' }}</small>
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

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
</body>
</html>
