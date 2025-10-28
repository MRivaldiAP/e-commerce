@php
    $themeName = $theme ?? 'theme-restoran';
    $settings = \App\Models\PageSetting::forPage('contact');
    $detailItems = json_decode($settings['details.items'] ?? '[]', true);
    if (! is_array($detailItems)) {
        $detailItems = [];
    }
    $socialItems = json_decode($settings['social.items'] ?? '[]', true);
    if (! is_array($socialItems)) {
        $socialItems = [];
    }
    $cartSummary = \App\Support\Cart::summary();
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = \App\Support\ThemeMedia::url($settings['hero.background'] ?? null);
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    if ($heroBackground) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.88), rgba(var(--theme-accent-rgb), 0.88)), url('{$heroBackground}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
        }
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.88), rgba(var(--theme-accent-rgb), 0.88));'
            : 'background: var(--theme-accent);';
    }
    $visibleSocials = collect($socialItems)->filter(function ($item) {
        return ($item['visible'] ?? '1') !== '0';
    });
    $mapEmbed = $settings['map.embed'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kontak Kami - Restoran Theme</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
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
    <style>
        .contact-card i {font-size: 2rem; color: #FEA116;}
        .contact-card {transition: transform .2s ease, box-shadow .2s ease;}
        .contact-card:hover {transform: translateY(-6px); box-shadow: 0 18px 40px rgba(var(--theme-accent-rgb), .12);}
        .contact-social a {width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid rgba(255,255,255,.3); margin: 0 .35rem; transition: all .2s ease;}
        .contact-social a:hover {background: #FEA116; border-color: #FEA116; color: var(--theme-accent);}
        .contact-map iframe {width: 100%; border: 0; min-height: 420px; border-radius: 16px;}
    </style>
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
        @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeRestoran::components.contact.sections.hero', [
            'settings' => $settings,
            'heroClasses' => $heroClasses,
            'heroStyle' => $heroStyle,
        ])
    </div>

    @includeWhen(($settings['details.visible'] ?? '1') == '1', 'themeRestoran::components.contact.sections.details', [
        'settings' => $settings,
        'detailItems' => $detailItems,
    ])

    @includeWhen(($settings['social.visible'] ?? '1') == '1', 'themeRestoran::components.contact.sections.social', [
        'settings' => $settings,
        'socialItems' => $visibleSocials,
    ])

    @includeWhen(($settings['map.visible'] ?? '1') == '1' && ! empty($mapEmbed), 'themeRestoran::components.contact.sections.map', [
        'settings' => $settings,
        'mapEmbed' => $mapEmbed,
    ])

    @include('themeRestoran::components.footer', ['footer' => $footerConfig])
    @include('themeRestoran::components.floating-contact-buttons', ['theme' => $themeName])

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
