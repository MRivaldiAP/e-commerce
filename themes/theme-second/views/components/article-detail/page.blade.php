@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use Illuminate\Support\Carbon;

    $themeName = $theme ?? 'theme-second';
    $detailSettings = array_merge(PageSetting::forPage('article-detail'), $settings ?? []);
    $article = $article ?? [];
    $recommended = collect($recommended ?? []);
    $meta = $meta ?? [];

    $dateObject = $article['date_object'] ?? null;
    if (! $dateObject && ! empty($article['date'])) {
        try {
            $dateObject = Carbon::parse($article['date']);
        } catch (\Exception $exception) {
            $dateObject = null;
        }
    }
    $dateFormatted = $article['date_formatted'] ?? ($dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null);

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $pageTitle = $meta['title'] ?? ($article['title'] ?? 'Artikel');
    $detailOgImage = ThemeMedia::url($article['image'] ?? null)
        ?? ThemeMedia::url($detailSettings['hero.image'] ?? null);

    $heroBackground = ThemeMedia::url($detailSettings['hero.image'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/breadcrumb.jpg');
    $heroMasked = ($detailSettings['hero.mask'] ?? '0') === '1';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    @if(!empty($meta['description'] ?? ''))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:title" content="{{ $pageTitle }}">
    @if($detailOgImage)
        <meta property="og:image" content="{{ $detailOgImage }}">
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

@includeWhen(($detailSettings['hero.visible'] ?? '1') === '1', 'themeSecond::components.article-detail.sections.hero', [
    'settings' => $detailSettings,
    'article' => $article,
    'heroBackground' => $heroBackground,
    'heroMasked' => $heroMasked,
])

<section id="content" class="blog-details spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8">
                @include('themeSecond::components.article-detail.sections.content', [
                    'settings' => $detailSettings,
                    'article' => $article,
                    'dateFormatted' => $dateFormatted,
                ])

                @include('themeSecond::components.article-detail.sections.comments', [
                    'settings' => $detailSettings,
                ])
            </div>
            <div class="col-lg-4 col-md-4">
                @includeWhen(($detailSettings['recommendations.visible'] ?? '1') === '1' && $recommended->isNotEmpty(), 'themeSecond::components.article-detail.sections.recommendations', [
                    'settings' => $detailSettings,
                    'recommended' => $recommended,
                ])
            </div>
        </div>
    </div>
</section>

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
