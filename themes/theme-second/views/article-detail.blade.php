<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Artikel' }}</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Carbon;

    $themeName = $theme ?? 'theme-second';
    $detailSettings = PageSetting::forPage('article-detail');
    $listSettings = PageSetting::forPage('article');

    $rawArticles = collect($articles ?? json_decode($listSettings['articles.items'] ?? '[]', true));
    $preparedArticles = $rawArticles->filter(function ($item) {
        return !empty($item['slug']);
    })->map(function ($item) {
        $date = null;
        if (!empty($item['date'])) {
            try {
                $date = Carbon::parse($item['date']);
            } catch (\Exception $e) {
                $date = null;
            }
        }
        $item['date_object'] = $date;
        $item['date_formatted'] = $date ? $date->locale(app()->getLocale())->isoFormat('D MMMM Y') : null;
        return $item;
    });

    $currentSlug = $article['slug'] ?? null;
    $current = $preparedArticles->firstWhere('slug', $currentSlug) ?? $article;

    if (!is_array($current)) {
        $current = (array) $current;
    }

    $dateObject = null;
    if (!empty($current['date_object'])) {
        $dateObject = $current['date_object'];
    } elseif (!empty($current['date'])) {
        try {
            $dateObject = Carbon::parse($current['date']);
        } catch (\Exception $e) {
            $dateObject = null;
        }
    }

    $dateFormatted = $dateObject ? $dateObject->locale(app()->getLocale())->isoFormat('D MMMM Y') : null;

    $pageTitle = $current['title'] ?? 'Artikel';

    $recommended = $preparedArticles->filter(function ($item) use ($currentSlug) {
        return $item['slug'] !== $currentSlug;
    })->sortByDesc(function ($item) {
        return optional($item['date_object'])->timestamp ?? 0;
    })->take(3);

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    function article_image_path($path) {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($detailSettings['hero.visible'] ?? '1') == '1')
<section id="hero" class="breadcrumb-section set-bg {{ ($detailSettings['hero.mask'] ?? '0') == '1' ? 'breadcrumb-section--mask' : '' }}" data-setbg="{{ !empty($detailSettings['hero.image']) ? asset('storage/'.$detailSettings['hero.image']) : asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $current['title'] ?? ($detailSettings['hero.title'] ?? 'Artikel') }}</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <a href="{{ route('articles.index') }}">Artikel</a>
                        <span>{{ $detailSettings['hero.title'] ?? ($current['title'] ?? 'Detail Artikel') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section id="content" class="blog-details spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8">
                <div class="blog__details__text">
                    @php $image = article_image_path($current['image'] ?? null); @endphp
                    @if($image)
                        <img src="{{ $image }}" alt="{{ $current['title'] ?? 'Artikel' }}" class="img-fluid mb-4">
                    @endif
                    <h2>{{ $current['title'] ?? 'Artikel' }}</h2>
                    <ul class="blog__details__author">
                        @if(($detailSettings['meta.show_author'] ?? '1') == '1' && !empty($current['author']))
                            <li><i class="fa fa-user"></i> {{ $current['author'] }}</li>
                        @endif
                        @if(($detailSettings['meta.show_date'] ?? '1') == '1' && $dateFormatted)
                            <li><i class="fa fa-calendar-o"></i> {{ $dateFormatted }}</li>
                        @endif
                    </ul>
                    <div class="blog__details__content">
                        {!! $current['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
                    </div>
                </div>

                <div id="comments" class="blog__details__comment mt-5">
                    @if(($detailSettings['comments.visible'] ?? '1') == '1')
                        <h4>{{ $detailSettings['comments.heading'] ?? 'Komentar' }}</h4>
                        <p class="text-muted">Fitur komentar akan segera hadir.</p>
                    @else
                        <div class="alert alert-light border">{{ $detailSettings['comments.disabled_text'] ?? 'Komentar dinonaktifkan.' }}</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 col-md-4">
                @if(($detailSettings['recommendations.visible'] ?? '1') == '1' && $recommended->isNotEmpty())
                <div id="recommendations" class="blog__sidebar">
                    <div class="blog__sidebar__item">
                        <h4>{{ $detailSettings['recommendations.heading'] ?? 'Artikel Lainnya' }}</h4>
                        <div class="blog__sidebar__recent">
                            @foreach($recommended as $item)
                                <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" class="blog__sidebar__recent__item">
                                    <h6>{{ $item['title'] ?? 'Artikel' }}</h6>
                                    <span>{{ $item['date_formatted'] ?? '' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>

@once
    <style>
        .breadcrumb-section--mask::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
        }
        .blog__details__author {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
            display: flex;
            gap: 1.5rem;
        }
        .blog__details__author li {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .blog__details__author i {
            margin-right: 0.5rem;
        }
        .blog__details__content p {
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }
    </style>
@endonce
</body>
</html>
