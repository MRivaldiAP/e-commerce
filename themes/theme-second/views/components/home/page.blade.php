@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-second';
    $settings = PageSetting::forPage('home');
    $activeSections = PageElements::activeSectionKeys($themeName, $settings);
    $products = Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    if (! is_array($testimonials)) {
        $testimonials = [];
    }
    $services = json_decode($settings['services.items'] ?? '[]', true);
    if (! is_array($services)) {
        $services = [];
    }

    $aboutImage = ThemeMedia::url($settings['about.image'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/blog/details/details-pic.jpg');
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/hero/banner.jpg');
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $contactMap = $settings['contact.map'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second Theme</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/style.css') }}" type="text/css">
    <style>
        .header {position: sticky; top: 0; z-index: 1000; background: #fff;}

        .about__row { align-items: center; }

        .about__media { position: relative; overflow: hidden; border-radius: 16px; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08); }

        .about__media img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .about__content { display: flex; flex-direction: column; gap: 1rem; }

        .about__content h2,
        .about__content h4 { font-size: 2rem; font-weight: 700; line-height: 1.2; }

        .about__content p { margin-bottom: 0; line-height: 1.7; }

        @media (max-width: 991.98px) {
            .about__content { text-align: center; align-items: center; }
        }
    </style>
</head>
<body>
@include('themeSecond::components.nav-menu', [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])

@foreach ($activeSections as $sectionKey)
    @switch($sectionKey)
        @case('hero')
            @includeWhen(($settings['hero.visible'] ?? '1') === '1', 'themeSecond::components.home.sections.hero', [
                'settings' => $settings,
                'heroBackground' => $heroBackground,
            ])
            @break

        @case('about')
            @includeWhen(($settings['about.visible'] ?? '1') === '1', 'themeSecond::components.home.sections.about', [
                'settings' => $settings,
                'aboutImage' => $aboutImage,
            ])
            @break

        @case('products')
            @includeWhen(($settings['products.visible'] ?? '1') === '1', 'themeSecond::components.home.sections.products', [
                'settings' => $settings,
                'products' => $products,
                'theme' => $themeName,
            ])
            @break

        @case('services')
            @includeWhen(($settings['services.visible'] ?? '1') === '1' && count($services), 'themeSecond::components.home.sections.services', [
                'settings' => $settings,
                'services' => $services,
            ])
            @break

        @case('testimonials')
            @includeWhen(($settings['testimonials.visible'] ?? '1') === '1' && count($testimonials), 'themeSecond::components.home.sections.testimonials', [
                'testimonials' => $testimonials,
                'theme' => $themeName,
            ])
            @break

        @case('contact')
            @includeWhen(($settings['contact.visible'] ?? '1') === '1', 'themeSecond::components.home.sections.contact', [
                'settings' => $settings,
                'contactMap' => $contactMap,
            ])
            @break
    @endswitch
@endforeach

@include('themeSecond::components.footer', ['footer' => $footerConfig])

<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/main.js') }}"></script>
<script>
    document.querySelectorAll('[data-setbg]').forEach(function (el) {
        const bg = el.getAttribute('data-setbg');
        if (bg) {
            el.style.backgroundImage = `url(${bg})`;
        }
    });
</script>

@include('themeSecond::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
