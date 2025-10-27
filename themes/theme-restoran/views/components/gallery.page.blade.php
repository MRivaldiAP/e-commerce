@php
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-restoran';
    $pageSettings = PageSetting::forPage('gallery');
    $settings = array_merge($pageSettings, $settings ?? []);
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $categoryCollection = collect($categories ?? GalleryCategory::orderBy('name')->get());
    $itemCollection = collect($items ?? GalleryItem::with('category')
        ->orderByRaw('position IS NULL')
        ->orderBy('position')
        ->orderBy('created_at', 'desc')
        ->get());

    $activeSections = PageElements::activeSectionKeys($themeName, $settings);
    $heroActive = in_array('hero', $activeSections, true);
    $filtersActive = in_array('filters', $activeSections, true);
    $gridActive = in_array('grid', $activeSections, true);

    $hasUncategorized = $itemCollection->contains(fn ($item) => $item->category === null);

    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? $settings['hero.background'] ?? null)
        ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
    $heroHeading = $settings['hero.heading'] ?? 'Galeri';
    $heroDescription = $settings['hero.description'] ?? null;
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');

    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }

    if ($heroBackground) {
        $heroStyle = $heroMaskEnabled
            ? "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.85), rgba(var(--theme-accent-rgb), 0.85)), url('{$heroBackground}'); background-size: cover; background-position: center;"
            : "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.85), rgba(var(--theme-accent-rgb), 0.85));'
            : 'background: var(--theme-accent);';
    }

    $filterVisible = $filtersActive
        && ($settings['filters.visible'] ?? '1') === '1'
        && ($categoryCollection->isNotEmpty() || $hasUncategorized);
    $filterHeading = $settings['filters.heading'] ?? 'Kategori Galeri';
    $allLabel = $settings['filters.all_label'] ?? 'Semua Foto';
    $gridHeading = $settings['grid.heading'] ?? 'Galeri Kami';
    $emptyText = $settings['grid.empty_text'] ?? 'Belum ada foto untuk ditampilkan.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $settings['hero.heading'] ?? 'Galeri' }}</title>
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

    @if($heroActive && ($settings['hero.visible'] ?? '1') === '1')
        @include('themeRestoran::components.gallery.sections.hero', [
            'heroClasses' => $heroClasses,
            'heroStyle' => $heroStyle,
            'heroHeading' => $heroHeading,
            'heroDescription' => $heroDescription,
        ])
    @endif
</div>

@if($filtersActive || $gridActive)
    @include('themeRestoran::components.gallery.sections.list', [
        'filterVisible' => $filterVisible,
        'filterHeading' => $filterHeading,
        'allLabel' => $allLabel,
        'gridHeading' => $gridActive ? $gridHeading : null,
        'emptyText' => $gridActive ? $emptyText : null,
        'categoryCollection' => $categoryCollection,
        'itemCollection' => $gridActive ? $itemCollection : collect(),
        'hasUncategorized' => $hasUncategorized,
        'gridActive' => $gridActive,
    ])
@endif

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
