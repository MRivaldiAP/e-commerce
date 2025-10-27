@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-second';
    $settings = PageSetting::forPage('contact');
    $activeSections = PageElements::activeSectionKeys('contact', $themeName, $settings);
    $detailItems = json_decode($settings['details.items'] ?? '[]', true);
    if (! is_array($detailItems)) {
        $detailItems = [];
    }
    $socialItems = json_decode($settings['social.items'] ?? '[]', true);
    if (! is_array($socialItems)) {
        $socialItems = [];
    }

    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $heroBackground = ThemeMedia::url($settings['hero.background'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/breadcrumb.jpg');
    $mapEmbed = $settings['map.embed'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami</title>
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
        .contact__widget__item i {font-size: 32px; color: #7fad39;}
        .contact__social a {display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 50%; border: 1px solid #e8e8e8; transition: all .2s ease;}
        .contact__social a:hover {background: #7fad39; color: #fff; border-color: #7fad39;}
        .contact__map iframe {width: 100%; border: 0; min-height: 420px;}
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
            @includeWhen(($settings['hero.visible'] ?? '1') === '1', 'themeSecond::components.contact.sections.hero', [
                'settings' => $settings,
                'heroBackground' => $heroBackground,
            ])
            @break

        @case('details')
            @includeWhen(($settings['details.visible'] ?? '1') === '1', 'themeSecond::components.contact.sections.details', [
                'settings' => $settings,
                'detailItems' => $detailItems,
            ])
            @break

        @case('social')
            @includeWhen(($settings['social.visible'] ?? '1') === '1', 'themeSecond::components.contact.sections.social', [
                'settings' => $settings,
                'socialItems' => $socialItems,
            ])
            @break

        @case('map')
            @includeWhen(($settings['map.visible'] ?? '1') === '1' && ! empty($mapEmbed), 'themeSecond::components.contact.sections.map', [
                'settings' => $settings,
                'mapEmbed' => $mapEmbed,
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
