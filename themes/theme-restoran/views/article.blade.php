<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Artikel' }}</title>
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
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use Illuminate\Support\Carbon;

    $themeName = $theme ?? 'theme-restoran';
    $settings = PageSetting::forPage('article');
    $rawArticles = collect(json_decode($settings['articles.items'] ?? '[]', true));

    $allArticles = $rawArticles->filter(fn ($item) => !empty($item['slug']))->map(function ($item) {
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
        $item['year'] = $date ? (int) $date->format('Y') : null;
        $item['month'] = $date ? $date->format('m') : null;
        $item['month_name'] = $date ? $date->locale(app()->getLocale())->isoFormat('MMMM') : null;
        return $item;
    });

    $timeline = $allArticles->filter(fn ($item) => $item['year'] && $item['month'])->groupBy('year')->sortKeysDesc()->map(function ($group) {
        return $group->groupBy('month')->sortKeysDesc()->map(function ($monthGroup) {
            $first = $monthGroup->first();
            return [
                'name' => $first['month_name'] ?? '',
                'articles' => $monthGroup->sortByDesc(function ($article) {
                    return optional($article['date_object'])->timestamp ?? 0;
                })->values(),
            ];
        });
    });

    $articles = $allArticles;

    if ($search = trim(request('search', ''))) {
        $lower = mb_strtolower($search);
        $articles = $articles->filter(function ($item) use ($lower) {
            $haystack = mb_strtolower(($item['title'] ?? '') . ' ' . ($item['excerpt'] ?? '') . ' ' . ($item['content'] ?? ''));
            return str_contains($haystack, $lower);
        });
    }

    if ($yearFilter = request('year')) {
        $articles = $articles->filter(fn ($item) => (string) ($item['year'] ?? '') === (string) $yearFilter);
    }

    if ($monthFilter = request('month')) {
        $monthFilter = str_pad($monthFilter, 2, '0', STR_PAD_LEFT);
        $articles = $articles->filter(fn ($item) => ($item['month'] ?? '') === $monthFilter);
    }

    $articles = $articles->sortByDesc(function ($article) {
        return optional($article['date_object'])->timestamp ?? 0;
    })->values();

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $pageTitle = $settings['hero.heading'] ?? 'Artikel';
    $buttonLabel = $settings['list.button_label'] ?? 'Baca Selengkapnya';
    $emptyText = $settings['list.empty_text'] ?? 'Belum ada artikel tersedia.';
    $searchPlaceholder = $settings['search.placeholder'] ?? 'Cari artikel...';

    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null) ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";

    function restoran_article_image($path) {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }
@endphp
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    @if(($settings['hero.visible'] ?? '1') == '1')
    <div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $settings['hero.heading'] ?? 'Artikel' }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">{{ $settings['hero.heading'] ?? 'Artikel' }}</li>
                </ol>
            </nav>
            @if(!empty($settings['hero.description']))
                <p class="text-white-50 mb-0">{{ $settings['hero.description'] }}</p>
            @endif
        </div>
    </div>
    @endif
</div>

<div id="list" class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            @forelse($articles as $article)
            <div class="card border-0 shadow-sm mb-4">
                @php $image = restoran_article_image($article['image'] ?? null); @endphp
                @if($image)
                    <img src="{{ $image }}" class="card-img-top" alt="{{ $article['title'] ?? 'Artikel' }}">
                @endif
                <div class="card-body p-4">
                    <div class="d-flex align-items-center text-muted mb-2 small">
                        @if(!empty($article['date_formatted']))
                            <span class="me-3"><i class="far fa-calendar-alt me-1"></i>{{ $article['date_formatted'] }}</span>
                        @endif
                        @if(!empty($article['author']))
                            <span><i class="far fa-user me-1"></i>{{ $article['author'] }}</span>
                        @endif
                    </div>
                    <h3 class="card-title h4">{{ $article['title'] ?? 'Artikel' }}</h3>
                    @if(!empty($article['excerpt']))
                        <p class="card-text">{{ $article['excerpt'] }}</p>
                    @endif
                    <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="btn btn-sm btn-primary">{{ $buttonLabel }}</a>
                </div>
            </div>
            @empty
            <div class="alert alert-light border">{{ $emptyText }}</div>
            @endforelse
        </div>
        <div class="col-lg-4">
            <div id="search" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title">Cari Artikel</h4>
                    <form method="GET" class="d-flex flex-column gap-3">
                        <input type="hidden" name="year" value="{{ request('year') }}">
                        <input type="hidden" name="month" value="{{ request('month') }}">
                        <input type="text" name="search" class="form-control" placeholder="{{ $searchPlaceholder }}" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </form>
                    @if(request()->filled('search') || request()->filled('year') || request()->filled('month'))
                        <a href="{{ route('articles.index') }}" class="btn btn-link px-0 mt-3">Reset Filter</a>
                    @endif
                </div>
            </div>
            @if(($settings['timeline.visible'] ?? '1') == '1')
            <div id="timeline" class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">{{ $settings['timeline.heading'] ?? 'Arsip' }}</h4>
                    @if($timeline->isEmpty())
                        <p class="text-muted mb-0">Belum ada arsip.</p>
                    @else
                        <div class="accordion" id="timelineAccordion">
                            @foreach($timeline as $year => $months)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $year }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $year }}" aria-expanded="false" aria-controls="collapse-{{ $year }}">
                                            {{ $year }}
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $year }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $year }}" data-bs-parent="#timelineAccordion">
                                        <div class="accordion-body">
                                            @foreach($months as $monthKey => $monthData)
                                                <div class="mb-3">
                                                    <a href="{{ route('articles.index', ['year' => $year, 'month' => $monthKey]) }}" class="fw-semibold d-block">{{ $monthData['name'] ?? $monthKey }} ({{ $monthData['articles']->count() }})</a>
                                                    <ul class="list-unstyled ms-3 mt-2">
                                                        @foreach($monthData['articles'] as $item)
                                                            <li class="mb-1"><a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" class="text-decoration-none"><i class="far fa-file-alt me-2 text-primary"></i>{{ $item['title'] ?? 'Artikel' }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

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
