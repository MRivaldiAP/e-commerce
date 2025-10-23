@php
    $themeName = $theme ?? 'theme-restoran';
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
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/palette.blade.php'), ['theme' => $themeName])->render() !!}
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
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use Illuminate\Support\Str;

    $settings = PageSetting::forPage('contact');
    $detailItems = json_decode($settings['details.items'] ?? '[]', true);
    $socialItems = json_decode($settings['social.items'] ?? '[]', true);
    if (!is_array($detailItems)) {
        $detailItems = [];
    }
    if (!is_array($socialItems)) {
        $socialItems = [];
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
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.88), rgba(var(--theme-accent-rgb), 0.88)), url('{$heroBackground}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
        }
    } elseif (! $heroMaskEnabled) {
        $heroStyle = 'background-image: none;';
    }

    $visibleSocials = collect($socialItems)->filter(function ($item) {
        return ($item['visible'] ?? '1') !== '0';
    });

    $mapEmbed = $settings['map.embed'] ?? '';

    $formatLink = function (?string $value, ?string $link) {
        if (! $value) {
            return '';
        }
        if ($link) {
            return '<a href="' . e($link) . '" target="_blank" rel="noopener" class="text-decoration-none text-white-50">' . e($value) . '</a>';
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '<a href="mailto:' . e($value) . '" class="text-decoration-none text-white-50">' . e($value) . '</a>';
        }
        if (Str::startsWith($value, '+') || preg_match('/^[0-9\s()+-]+$/', $value)) {
            return '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $value)) . '" class="text-decoration-none text-white-50">' . e($value) . '</a>';
        }
        return e($value);
    };
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
            <h1 class="display-4 text-white mb-3">{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h1>
            @if(!empty($settings['hero.description']))
            <p class="text-white-50 mb-0">{{ $settings['hero.description'] }}</p>
            @endif
        </div>
    </div>
    @endif
</div>

@if(($settings['details.visible'] ?? '1') == '1')
<div id="details" class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h5>
            @if(!empty($settings['details.description']))
            <p class="text-muted">{{ $settings['details.description'] }}</p>
            @endif
        </div>
        <div class="row g-4 justify-content-center">
            @forelse($detailItems as $item)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="contact-card bg-dark text-white text-center rounded py-4 px-3 h-100">
                    @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }} mb-3" aria-hidden="true"></i>
                    @else
                    <i class="fa fa-map-marker-alt mb-3" aria-hidden="true"></i>
                    @endif
                    @if(!empty($item['label']))
                    <h5 class="text-uppercase text-primary mb-2">{{ $item['label'] }}</h5>
                    @endif
                    @if(!empty($item['value']))
                    <p class="mb-0 text-white-50">{!! $formatLink($item['value'], $item['link'] ?? null) !!}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p class="text-muted">Informasi kontak belum tersedia.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endif

@if(($settings['social.visible'] ?? '1') == '1')
<div id="social" class="container-xxl py-5 bg-dark">
    <div class="container text-center">
        <h5 class="section-title ff-secondary text-primary fw-normal">{{ $settings['social.heading'] ?? 'Ikuti Kami' }}</h5>
        @if(!empty($settings['social.description']))
        <p class="text-white-50 mb-4">{{ $settings['social.description'] }}</p>
        @endif
        <div class="contact-social">
            @forelse($visibleSocials as $item)
                @php $url = $item['url'] ?? '#'; @endphp
                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-white">
                    @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }}"></i>
                    @else
                    <i class="fa fa-link"></i>
                    @endif
                </a>
            @empty
                <span class="text-white-50">Belum ada tautan media sosial yang ditampilkan.</span>
            @endforelse
        </div>
    </div>
</div>
@endif

@if(($settings['map.visible'] ?? '1') == '1' && !empty($mapEmbed))
<div id="map" class="container-xxl py-5">
    <div class="container">
        @if(!empty($settings['map.heading']))
        <div class="text-center mb-4">
            <h5 class="section-title ff-secondary text-primary fw-normal">{{ $settings['map.heading'] }}</h5>
        </div>
        @endif
        <div class="contact-map">
            @if(Str::contains($mapEmbed, ['<iframe', '<IFRAME']))
                {!! $mapEmbed !!}
            @else
                <iframe src="{{ $mapEmbed }}" allowfullscreen="" loading="lazy"></iframe>
            @endif
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
