@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? ($settings['hero.heading'] ?? 'Artikel') }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? ($settings['hero.heading'] ?? 'Artikel') }}">
    @php
        $settings = $settings ?? [];
        $meta = $meta ?? [];
        $articles = collect($articles ?? [])->filter(fn ($item) => !empty($item['slug'] ?? null));
        $timeline = collect($timeline ?? []);
        $filters = $filters ?? [
            'search' => request('search'),
            'year' => request('year'),
            'month' => request('month'),
        ];

        $resolveArticleImage = function ($path) {
            if (empty($path)) {
                return null;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/' . ltrim($path, '/'));
        };

        $collectionForMeta = $articles;
        $listOgImage = null;
        $firstImage = $collectionForMeta->first()['image'] ?? null;
        if (!empty($firstImage)) {
            $listOgImage = $resolveArticleImage($firstImage);
        } elseif (!empty($settings['hero.image'] ?? null)) {
            $listOgImage = $resolveArticleImage($settings['hero.image']);
        }

        $pageTitle = $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel');
    @endphp
    @if($listOgImage)
        <meta property="og:image" content="{{ $listOgImage }}">
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
    @include('themeRestoran::components.palette', ['theme' => $themeName])
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null) ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
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
        'visible' => ($settings['hero.visible'] ?? '1') === '1',
        'classes' => $heroClasses,
        'style' => $heroStyle,
        'title' => $pageTitle,
        'description' => $settings['hero.description'] ?? null,
    ];

    $listSection = [
        'articles' => $articles->map(function ($article) use ($resolveArticleImage) {
            $slug = $article['slug'] ?? null;
            return [
                'title' => $article['title'] ?? 'Artikel',
                'excerpt' => $article['excerpt'] ?? null,
                'date' => $article['date_formatted'] ?? null,
                'author' => $article['author'] ?? null,
                'image' => $resolveArticleImage($article['image'] ?? null),
                'url' => $slug ? route('articles.show', ['slug' => $slug]) : '#',
            ];
        })->values()->all(),
        'button_label' => $settings['list.button_label'] ?? 'Baca Selengkapnya',
        'empty_text' => $settings['list.empty_text'] ?? 'Belum ada artikel tersedia.',
        'search' => [
            'placeholder' => $settings['search.placeholder'] ?? 'Cari artikel...',
            'filters' => $filters,
        ],
    ];

    $timelineSection = [
        'visible' => ($settings['timeline.visible'] ?? '1') === '1' && $timeline->isNotEmpty(),
        'heading' => $settings['timeline.heading'] ?? 'Arsip Artikel',
        'items' => $timeline->map(function ($item) {
            $year = $item['year'] ?? null;
            $month = $item['month'] ?? null;
            return [
                'label' => $item['label'] ?? trim(($item['month_name'] ?? '') . ' ' . ($item['year'] ?? '')),
                'count' => $item['count'] ?? 0,
                'url' => route('articles.index', array_filter([
                    'year' => $year,
                    'month' => $month,
                ])),
            ];
        })->values()->all(),
    ];
@endphp
<div class="container-xxl position-relative p-0">
    @include('themeRestoran::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @include('themeRestoran::components.article.sections.hero', ['hero' => $heroSection])
</div>

@include('themeRestoran::components.article.sections.list', [
    'list' => $listSection,
    'timeline' => $timelineSection,
])

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
