@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-restoran';
    $pageSettings = PageSetting::forPage('article-detail', $themeName);
    $detailSettings = array_merge($pageSettings, $settings ?? []);
    $article = $article ?? [];
    $recommended = collect($recommended ?? []);
    $meta = $meta ?? [];

    $resolveArticleImage = static function ($path) {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $detailOgImage = null;
    $primaryImage = $article['image'] ?? null;

    if ($primaryImage) {
        $detailOgImage = $resolveArticleImage($primaryImage);
    } elseif (! empty($detailSettings['hero.image'] ?? null)) {
        $detailOgImage = $resolveArticleImage($detailSettings['hero.image']);
    }

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $activeSections = PageElements::activeSectionKeys('article-detail', $themeName, $detailSettings);
    $heroActive = in_array('hero', $activeSections, true);
    $metaActive = in_array('meta', $activeSections, true);
    $commentsActive = in_array('comments', $activeSections, true);
    $recommendationsActive = in_array('recommendations', $activeSections, true);

    $dateObject = $article['date_object'] ?? null;

    if (! $dateObject && ! empty($article['date'])) {
        try {
            $dateObject = \Illuminate\Support\Carbon::parse($article['date']);
        } catch (\Exception $e) {
            $dateObject = null;
        }
    }

    $dateFormatted = $article['date_formatted']
        ?? ($dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null);

    $heroMaskEnabled = ($detailSettings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($detailSettings['hero.image'] ?? null)
        ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');

    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }

    if ($heroBackground) {
        $heroStyle = $heroMaskEnabled
            ? "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroBackground}'); background-size: cover; background-position: center;"
            : "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9));'
            : 'background: var(--theme-accent);';
    }

    $heroSection = [
        'visible' => $heroActive && ($detailSettings['hero.visible'] ?? '1') === '1',
        'classes' => $heroClasses,
        'style' => $heroStyle,
        'title' => $article['title'] ?? ($detailSettings['hero.title'] ?? 'Artikel'),
        'breadcrumbTitle' => $detailSettings['hero.title'] ?? ($article['title'] ?? 'Detail'),
    ];

    $articleSection = [
        'title' => $article['title'] ?? 'Artikel',
        'image' => $resolveArticleImage($article['image'] ?? null),
        'content' => $article['content'] ?? '<p>Konten artikel belum tersedia.</p>',
        'meta' => [
            'show_date' => $metaActive && ($detailSettings['meta.show_date'] ?? '1') === '1' && ! empty($dateFormatted),
            'date' => $dateFormatted,
            'show_author' => $metaActive && ($detailSettings['meta.show_author'] ?? '1') === '1' && ! empty($article['author']),
            'author' => $article['author'] ?? null,
        ],
    ];

    $commentsSection = [
        'visible' => $commentsActive && ($detailSettings['comments.visible'] ?? '1') === '1',
        'heading' => $detailSettings['comments.heading'] ?? 'Komentar',
        'disabled_text' => $detailSettings['comments.disabled_text'] ?? 'Komentar dinonaktifkan.',
    ];

    $recommendationsSection = [
        'visible' => $recommendationsActive && ($detailSettings['recommendations.visible'] ?? '1') === '1' && $recommended->isNotEmpty(),
        'heading' => $detailSettings['recommendations.heading'] ?? 'Artikel Lainnya',
        'items' => $recommended->map(function ($item) {
            $slug = $item['slug'] ?? null;

            return [
                'title' => $item['title'] ?? 'Artikel',
                'date' => $item['date_formatted'] ?? null,
                'url' => $slug ? route('articles.show', ['slug' => $slug]) : '#',
            ];
        })->values()->all(),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}</title>
    @if(! empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    @if($detailOgImage)
        <meta property="og:image" content="{{ $detailOgImage }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($article['title'] ?? 'Artikel') }}">
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
    @include('themeRestoran::components.palette', ['theme' => $themeName])
</head>
<body>
<div class="container-xxl position-relative p-0">
    @include('themeRestoran::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @if($heroSection['visible'] ?? false)
        @include('themeRestoran::components.article-detail.sections.hero', ['hero' => $heroSection])
    @endif
</div>

<div id="content" class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            @include('themeRestoran::components.article-detail.sections.content', [
                'article' => $articleSection,
            ])

            @if($commentsSection['visible'] ?? false)
                @include('themeRestoran::components.article-detail.sections.comments', ['comments' => $commentsSection])
            @endif
        </div>
        <div class="col-lg-4">
            @if($recommendationsSection['visible'] ?? false)
                @include('themeRestoran::components.article-detail.sections.recommendations', ['recommendations' => $recommendationsSection])
            @endif
        </div>
    </div>
</div>

@include('themeRestoran::components.footer', ['footer' => $footerConfig])

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

@include('themeRestoran::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
