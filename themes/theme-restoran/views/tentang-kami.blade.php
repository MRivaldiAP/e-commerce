@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tentang Kami - Restoran Theme</title>
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
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/palette.blade.php'), ['theme' => $themeName])->render() !!}
    <style>
        .hero-header {background-size: cover; background-position: center;}
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $settings = PageSetting::forPage('about');
    $teamMembers = json_decode($settings['team.members'] ?? '[]', true);
    $advantages = json_decode($settings['advantages.items'] ?? '[]', true);
    if (!is_array($teamMembers)) {
        $teamMembers = [];
    }
    if (!is_array($advantages)) {
        $advantages = [];
    }
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.background'] ?? null);
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    if ($heroBackground) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroBackground}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
        }
    } elseif (! $heroMaskEnabled) {
        $heroStyle = 'background-image: none;';
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
            <h1 class="display-4 text-white mb-3">{{ $settings['hero.heading'] ?? 'Tentang Kami' }}</h1>
            @if(!empty($settings['hero.text']))
            <p class="text-white-50 mb-0">{{ $settings['hero.text'] }}</p>
            @endif
        </div>
    </div>
    @endif
</div>

@if(($settings['intro.visible'] ?? '1') == '1')
<div id="intro" class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                @php $image = ThemeMedia::url($settings['intro.image'] ?? null); @endphp
                <div class="position-relative">
                    <img class="img-fluid rounded w-100" src="{{ $image ?: asset('storage/themes/theme-restoran/img/about-1.jpg') }}" alt="{{ $settings['intro.heading'] ?? 'Tentang Kami' }}">
                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-primary px-3 py-2">{{ $settings['hero.heading'] ?? 'Tentang Kami' }}</span>
                </div>
            </div>
            <div class="col-lg-6">
                <h5 class="section-title ff-secondary text-start text-primary fw-normal">{{ $settings['intro.heading'] ?? 'Cerita Kami' }}</h5>
                <p class="mb-4">{{ $settings['intro.description'] ?? 'Kami menyajikan pengalaman kuliner terbaik dengan bahan pilihan dan layanan penuh kehangatan.' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

@if(($settings['quote.visible'] ?? '1') == '1' && !empty($settings['quote.text']))
<div id="quote" class="container-xxl py-5 bg-dark">
    <div class="container text-center">
        <div class="mx-auto" style="max-width:720px;">
            <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
            <p class="fs-4 text-white-50">“{{ $settings['quote.text'] }}”</p>
            @if(!empty($settings['quote.author']))
            <h5 class="text-white mb-0">{{ $settings['quote.author'] }}</h5>
            @endif
        </div>
    </div>
</div>
@endif

@if(($settings['team.visible'] ?? '1') == '1' && count($teamMembers))
<div id="team" class="container-xxl pt-5 pb-3">
    <div class="container">
        <div class="text-center">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">{{ $settings['team.heading'] ?? 'Tim Kami' }}</h5>
            @if(!empty($settings['team.description']))
            <p class="text-muted mb-5">{{ $settings['team.description'] }}</p>
            @endif
        </div>
        <div class="row g-4">
            @foreach($teamMembers as $index => $member)
            @php $photo = ThemeMedia::url($member['photo'] ?? null); @endphp
            <div class="col-lg-3 col-md-6">
                <div class="team-item text-center rounded overflow-hidden h-100">
                    <div class="rounded-circle overflow-hidden m-4">
                        <img class="img-fluid" src="{{ $photo ?: asset('storage/themes/theme-restoran/img/team-' . (($index % 4) + 1) . '.jpg') }}" alt="{{ $member['name'] ?? '' }}">
                    </div>
                    <h5 class="mb-0">{{ $member['name'] ?? '' }}</h5>
                    <small>{{ $member['title'] ?? '' }}</small>
                    @if(!empty($member['description']))
                    <p class="px-3 mt-3 mb-0">{{ $member['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if(($settings['advantages.visible'] ?? '1') == '1' && count($advantages))
<div id="advantages" class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">{{ $settings['advantages.heading'] ?? 'Keunggulan Kami' }}</h5>
            @if(!empty($settings['advantages.description']))
            <p class="text-muted">{{ $settings['advantages.description'] }}</p>
            @endif
        </div>
        <div class="row g-4">
            @foreach($advantages as $advantage)
            <div class="col-lg-4 col-md-6">
                <div class="service-item rounded pt-3 h-100">
                    <div class="p-4">
                        @if(!empty($advantage['icon']))
                        <i class="{{ $advantage['icon'] }} text-primary fa-3x mb-3"></i>
                        @endif
                        <h5>{{ $advantage['title'] ?? '' }}</h5>
                        <p class="mb-0">{{ $advantage['text'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

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
