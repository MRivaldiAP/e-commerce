<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Tentang Kami') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-istudio';
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $settings = PageSetting::forPage('about');
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $normalizeChecklist = function ($items) {
        if (! is_array($items)) {
            return [];
        }

        $items = array_values(array_filter($items));

        $mapped = array_map(function ($item) {
            if (is_array($item)) {
                foreach (['text', 'label', 'value'] as $key) {
                    if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') {
                        return $item[$key];
                    }
                }

                $first = reset($item);

                return is_string($first) ? $first : '';
            }

            return is_string($item) ? $item : '';
        }, $items);

        return array_values(array_filter($mapped, fn ($value) => is_string($value) && trim($value) !== ''));
    };

    $introChecklist = $normalizeChecklist(json_decode($settings['intro.checklist'] ?? '[]', true));
    if ($introChecklist === []) {
        $introChecklist = ['Award Winning', 'Professional Staff', '24/7 Support', 'Fair Prices'];
    }

    $teamMembers = json_decode($settings['team.members'] ?? '[]', true);
    $teamMembers = is_array($teamMembers) ? array_values(array_filter($teamMembers, fn ($item) => is_array($item))) : [];
    if ($teamMembers === []) {
        $teamMembers = [
            ['name' => 'Boris Johnson', 'title' => 'Architect', 'photo' => $assetBase('img/team-1.jpg')],
            ['name' => 'Donald Pakura', 'title' => 'Architect', 'photo' => $assetBase('img/team-2.jpg')],
            ['name' => 'Bradley Gordon', 'title' => 'Architect', 'photo' => $assetBase('img/team-3.jpg')],
            ['name' => 'Alexander Bell', 'title' => 'Architect', 'photo' => $assetBase('img/team-4.jpg')],
        ];
    } else {
        $teamMembers = array_map(function ($member, $index) use ($resolveMedia, $assetBase) {
            $fallbacks = [
                $assetBase('img/team-1.jpg'),
                $assetBase('img/team-2.jpg'),
                $assetBase('img/team-3.jpg'),
                $assetBase('img/team-4.jpg'),
            ];
            $fallback = $fallbacks[$index % count($fallbacks)];
            $social = $member['social'] ?? [];
            if (is_string($social)) {
                $social = array_filter(array_map('trim', explode(',', $social)));
            }
            if (! is_array($social)) {
                $social = [];
            }

            return [
                'name' => $member['name'] ?? 'Team Member',
                'title' => $member['title'] ?? '',
                'photo' => $resolveMedia($member['photo'] ?? null, $fallback),
                'social' => $social,
            ];
        }, $teamMembers, array_keys($teamMembers));
    }

    $advantages = json_decode($settings['advantages.items'] ?? '[]', true);
    $advantages = is_array($advantages) ? array_values(array_filter($advantages, fn ($item) => is_array($item))) : [];
    if ($advantages === []) {
        $advantages = [
            ['icon' => 'fa fa-lightbulb', 'title' => 'Ide Kreatif', 'text' => 'Solusi desain inovatif yang disesuaikan dengan kebutuhan ruang Anda.'],
            ['icon' => 'fa fa-palette', 'title' => 'Palet Premium', 'text' => 'Kombinasi warna terbaik untuk menghadirkan suasana ruang yang hangat.'],
            ['icon' => 'fa fa-tools', 'title' => 'Eksekusi Rapi', 'text' => 'Tim profesional memastikan setiap detail terealisasi sempurna.'],
        ];
    }
@endphp
    <link href="{{ $assetBase('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($settings['hero.visible'] ?? '1') === '1')
    @php
        $heroBackground = $resolveMedia($settings['hero.background'] ?? null, $assetBase('img/hero-slider-1.jpg'));
        $heroHeading = $settings['hero.heading'] ?? 'About';
        $breadcrumbRaw = $settings['hero.breadcrumb'] ?? null;
        $breadcrumbItems = is_array($breadcrumbRaw) ? $breadcrumbRaw : null;
        if (! $breadcrumbItems) {
            $breadcrumbItems = [
                ['label' => 'Home', 'link' => url('/')],
                ['label' => 'Pages', 'link' => '#!'],
                ['label' => 'About', 'link' => null],
            ];
        }
    @endphp
    <div class="container-fluid pb-5 bg-primary hero-header" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container py-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <h1 class="display-1 mb-0 animated slideInLeft">{{ $heroHeading }}</h1>
                </div>
                <div class="col-lg-6 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            @foreach ($breadcrumbItems as $item)
                                @if(!empty($item['link']))
                                    <li class="breadcrumb-item"><a class="text-primary" href="{{ $item['link'] }}">{{ $item['label'] }}</a></li>
                                @else
                                    <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $item['label'] }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['intro.visible'] ?? '1') === '1')
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6 wow fadeIn" data-wow-delay="0.1s">
                            <img class="img-fluid" src="{{ $resolveMedia($settings['intro.image_primary'] ?? null, $assetBase('img/about-1.jpg')) }}" alt="{{ $settings['intro.heading'] ?? 'Tentang Kami' }}">
                        </div>
                        <div class="col-6 wow fadeIn" data-wow-delay="0.3s">
                            <img class="img-fluid h-75" src="{{ $resolveMedia($settings['intro.image_secondary'] ?? null, $assetBase('img/about-2.jpg')) }}" alt="{{ $settings['intro.heading'] ?? 'Tentang Kami' }}">
                            <div class="h-25 d-flex align-items-center text-center bg-primary px-4">
                                <h4 class="text-white lh-base mb-0">{{ $settings['intro.badge_text'] ?? 'Award Winning Studio Since 1990' }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                    <h1 class="mb-5">{!! $settings['intro.heading'] ?? '<span class="text-uppercase text-primary bg-light px-2">History</span> of Our Creation' !!}</h1>
                    <p class="mb-4">{{ $settings['intro.description_primary'] ?? 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum et tempor sit. Aliqu diam amet diam et eos labore.' }}</p>
                    <p class="mb-5">{{ $settings['intro.description_secondary'] ?? 'Clita erat ipsum et lorem et sit, sed stet no labore lorem sit. Sanctus clita duo justo et tempor.' }}</p>
                    <div class="row g-3">
                        @foreach ($introChecklist as $item)
                            <div class="col-sm-6">
                                <h6 class="mb-3"><i class="fa fa-check text-primary me-2"></i>{{ $item }}</h6>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center mt-4">
                        @if(!empty($settings['intro.button_label']))
                            <a class="btn btn-primary px-4 me-2" href="{{ $settings['intro.button_link'] ?? '#!' }}">{{ $settings['intro.button_label'] }}</a>
                        @endif
                        @php
                            $socialLinks = $settings['intro.social_links'] ?? [];
                            if (is_string($socialLinks)) {
                                $socialLinks = array_filter(array_map('trim', explode(',', $socialLinks)));
                            }
                            if (! is_array($socialLinks)) {
                                $socialLinks = [];
                            }
                        @endphp
                        @foreach ($socialLinks as $link)
                            <a class="btn btn-outline-primary btn-square border-2 me-2" href="{{ $link }}" target="_blank" rel="noopener">
                                <i class="fab fa-external-link-alt"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['quote.visible'] ?? '1') === '1')
    <div class="container-fluid bg-primary py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <i class="fa fa-quote-left fa-2x mb-3"></i>
                    <p class="fs-5">{{ $settings['quote.text'] ?? 'Design is not just what it looks like and feels like. Design is how it works.' }}</p>
                    @if(!empty($settings['quote.author']))
                        <h5 class="mt-3">{{ $settings['quote.author'] }}</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['team.visible'] ?? '1') === '1')
    <div class="container-fluid bg-light py-5">
        <div class="container py-5">
            <h1 class="mb-5">{!! $settings['team.heading'] ?? 'Our Professional <span class="text-uppercase text-primary bg-light px-2">Designers</span>' !!}</h1>
            <p class="mb-4">{{ $settings['team.description'] ?? 'Tim kami terdiri dari profesional kreatif dengan pengalaman luas di industri interior.' }}</p>
            <div class="row g-4">
                @foreach ($teamMembers as $member)
                    <div class="col-md-6 col-lg-3 wow fadeIn" data-wow-delay="0.{{ $loop->iteration }}s">
                        <div class="team-item position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="{{ $member['photo'] }}" alt="{{ $member['name'] }}">
                            <div class="team-overlay">
                                @if(!empty($member['title']))
                                    <small class="mb-2">{{ $member['title'] }}</small>
                                @endif
                                <h4 class="lh-base text-light">{{ $member['name'] }}</h4>
                                @if(!empty($member['social']))
                                    <div class="d-flex justify-content-center">
                                        @foreach ($member['social'] as $link)
                                            <a class="btn btn-outline-primary btn-sm-square border-2 me-2" href="{{ $link }}" target="_blank" rel="noopener">
                                                <i class="fab fa-external-link-alt"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if(($settings['advantages.visible'] ?? '1') === '1')
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h1>{!! $settings['advantages.heading'] ?? 'Mengapa Memilih <span class="text-uppercase text-primary bg-light px-2">Kami</span>' !!}</h1>
                <p class="mb-0">{{ $settings['advantages.description'] ?? 'Keunggulan layanan kami yang siap meningkatkan kualitas ruang Anda.' }}</p>
            </div>
            <div class="row g-4">
                @foreach ($advantages as $advantage)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 p-4 text-center">
                            <div class="icon mb-3">
                                <i class="{{ $advantage['icon'] ?? 'fa fa-star' }} fa-3x text-primary"></i>
                            </div>
                            <h4>{{ $advantage['title'] ?? '' }}</h4>
                            <p class="mb-0">{{ $advantage['text'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
    'brand' => $navigation['brand'],
])->render() !!}

    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ $assetBase('lib/wow/wow.min.js') }}"></script>
    <script src="{{ $assetBase('lib/easing/easing.min.js') }}"></script>
    <script src="{{ $assetBase('lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ $assetBase('lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ $assetBase('js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
