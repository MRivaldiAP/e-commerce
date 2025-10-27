@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-second';
    $pageSettings = PageSetting::forPage('article');
    $settings = array_merge($pageSettings, $settings ?? []);
    $meta = $meta ?? [];
    $articleCollection = collect($articles ?? [])->filter(fn ($item) => ! empty($item['slug'] ?? null));
    $timeline = collect($timeline ?? []);
    $filters = array_merge([
        'search' => request('search'),
        'year' => request('year'),
        'month' => request('month'),
    ], $filters ?? []);

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $pageTitle = $settings['hero.heading'] ?? ($meta['title'] ?? 'Artikel');
    $buttonLabel = $settings['list.button_label'] ?? 'Baca Selengkapnya';
    $emptyText = $settings['list.empty_text'] ?? 'Belum ada artikel untuk ditampilkan.';
    $searchPlaceholder = $settings['search.placeholder'] ?? 'Cari artikel...';

    $listOgImage = null;
    $firstImage = $articleCollection->first()['image'] ?? null;
    if ($firstImage) {
        $listOgImage = ThemeMedia::url($firstImage);
    }
    if (! $listOgImage && ! empty($settings['hero.image'] ?? null)) {
        $listOgImage = ThemeMedia::url($settings['hero.image']);
    }

    $activeSections = PageElements::activeSectionKeys($themeName, $settings);
    $timelineActive = in_array('timeline', $activeSections, true);
    $renderSections = array_values(array_filter($activeSections, function ($section) {
        return $section !== 'timeline';
    }));

    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/breadcrumb.jpg');
    $heroMasked = ($settings['hero.mask'] ?? '0') === '1';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? $pageTitle }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $meta['title'] ?? $pageTitle }}">
    @if($listOgImage)
        <meta property="og:image" content="{{ $listOgImage }}">
    @endif
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/style.css') }}" type="text/css">
</head>
<body>
@include('themeSecond::components.nav-menu', [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])

@foreach ($renderSections as $sectionKey)
    @switch($sectionKey)
        @case('hero')
            @includeWhen(($settings['hero.visible'] ?? '1') === '1', 'themeSecond::components.article.sections.hero', [
                'settings' => $settings,
                'pageTitle' => $pageTitle,
                'heroBackground' => $heroBackground,
                'heroMasked' => $heroMasked,
            ])
            @break

        @case('list')
            @include('themeSecond::components.article.sections.list', [
                'settings' => $settings,
                'articles' => $articleCollection,
                'filters' => $filters,
                'timeline' => $timeline,
                'buttonLabel' => $buttonLabel,
                'emptyText' => $emptyText,
                'searchPlaceholder' => $searchPlaceholder,
                'pageTitle' => $pageTitle,
                'theme' => $themeName,
                'timelineActive' => $timelineActive,
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
