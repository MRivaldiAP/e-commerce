@php
    $themeName = $theme ?? 'theme-restoran';
    $settings = \App\Models\PageSetting::forPage('home', $themeName);
    $activeSections = \App\Support\PageElements::activeSectionKeys('home', $themeName, $settings);
    $products = \App\Models\Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    if (! is_array($testimonials)) {
        $testimonials = [];
    }
    $services = json_decode($settings['services.items'] ?? '[]', true);
    if (! is_array($services)) {
        $services = [];
    }
    $aboutImage = $settings['about.image'] ?? null;
    $cartSummary = \App\Support\Cart::summary();
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    $heroImage = \App\Support\ThemeMedia::url($settings['hero.image'] ?? null);
    $heroSpinImage = \App\Support\ThemeMedia::url($settings['hero.spin_image'] ?? null);
    if ($heroImage) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroImage}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroImage}'); background-size: cover; background-position: center;";
        }
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9));'
            : 'background: var(--theme-accent);';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Restoran Theme</title>
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
        .navbar {position: sticky; top:0; z-index:1030;}
        .navbar-dark{background:var(--dark)!important;}
        .hero-header img.main{animation:none;}
        .hero-header img.spin{animation:imgRotate 50s linear infinite!important;}
        .hero-header .spin-text{animation:imgRotate 50s linear infinite;}
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
        @foreach ($activeSections as $sectionKey)
            @if($sectionKey === 'hero')
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeRestoran::components.home.sections.hero', [
                    'settings' => $settings,
                    'heroClasses' => $heroClasses,
                    'heroStyle' => $heroStyle,
                    'heroSpinImage' => $heroSpinImage,
                ])
            @endif
        @endforeach
    </div>

    @foreach ($activeSections as $sectionKey)
        @switch($sectionKey)
            @case('services')
                @includeWhen(($settings['services.visible'] ?? '1') == '1' && count($services), 'themeRestoran::components.home.sections.services', [
                    'services' => $services,
                ])
                @break

            @case('about')
                @includeWhen(($settings['about.visible'] ?? '1') == '1', 'themeRestoran::components.home.sections.about', [
                    'settings' => $settings,
                    'aboutImage' => $aboutImage,
                ])
                @break

            @case('products')
                @includeWhen(($settings['products.visible'] ?? '1') == '1', 'themeRestoran::components.home.sections.products', [
                    'settings' => $settings,
                    'products' => $products,
                ])
                @break

            @case('testimonials')
                @includeWhen(($settings['testimonials.visible'] ?? '1') == '1' && count($testimonials), 'themeRestoran::components.home.sections.testimonials', [
                    'testimonials' => $testimonials,
                ])
                @break

            @case('contact')
                @includeWhen(($settings['contact.visible'] ?? '1') == '1', 'themeRestoran::components.home.sections.contact', [
                    'settings' => $settings,
                ])
                @break
        @endswitch
    @endforeach

    @include('themeRestoran::components.footer', ['footer' => $footerConfig])
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
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
