@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-istudio';
    $settings = PageSetting::forPage('contact');
    $cartSummary = $cartSummary ?? Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $detailItems = json_decode($settings['details.items'] ?? '[]', true);
    $socialItems = json_decode($settings['social.items'] ?? '[]', true);
    if (!is_array($detailItems)) {
        $detailItems = [];
    }
    if (!is_array($socialItems)) {
        $socialItems = [];
    }

    $visibleSocials = collect($socialItems)->filter(function ($item) {
        return ($item['visible'] ?? '1') !== '0';
    });

    $mapEmbed = $settings['map.embed'] ?? '';

    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroBackground = $resolveMedia($settings['hero.background'] ?? null, $assetBase('img/hero-slider-1.jpg'));

    $formatLink = function (?string $value, ?string $link) {
        if (! $value) {
            return '';
        }
        if ($link) {
            return '<a href="' . e($link) . '" target="_blank" rel="noopener" class="text-decoration-none">' . e($value) . '</a>';
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '<a href="mailto:' . e($value) . '" class="text-decoration-none">' . e($value) . '</a>';
        }
        if (Str::startsWith($value, '+') || preg_match('/^[0-9\s()+-]+$/', $value)) {
            return '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $value)) . '" class="text-decoration-none">' . e($value) . '</a>';
        }

        return e($value);
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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

@if($heroVisible)
    <div id="hero" class="container-fluid pb-5 bg-primary hero-header" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container py-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <h1 class="display-1 mb-0 animated slideInLeft">{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h1>
                </div>
                <div class="col-lg-6 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</li>
                        </ol>
                    </nav>
                    @if(!empty($settings['hero.description']))
                        <p class="text-muted mt-3 mb-0">{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['details.visible'] ?? '1') === '1')
<section id="details" class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center wow fadeIn" data-wow-delay="0.1s">
            <h1 class="mb-4">{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h1>
            @if(!empty($settings['details.description']))
                <p class="text-muted">{{ $settings['details.description'] }}</p>
            @endif
        </div>
        <div class="row g-4 justify-content-center">
            @forelse($detailItems as $item)
                @php
                    $icon = $item['icon'] ?? 'fa fa-info-circle';
                    $label = $item['label'] ?? '';
                    $value = $item['value'] ?? '';
                    $link = $item['link'] ?? null;
                @endphp
                <div class="col-md-4 col-lg-3">
                    <div class="contact-card text-center h-100 border border-light bg-white shadow-sm p-4 rounded">
                        <div class="contact-card__icon mb-3">
                            <i class="{{ $icon }} text-primary fs-1"></i>
                        </div>
                        @if($label)
                            <h5 class="text-uppercase mb-2">{{ $label }}</h5>
                        @endif
                        @if($value)
                            <p class="mb-0 text-muted">{!! $formatLink($value, $link) !!}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center">Informasi kontak belum tersedia.</div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endif

<section id="contact-form" class="container-fluid py-5 bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h2 class="mb-3">{{ $settings['hero.description'] ?? 'Ada pertanyaan? Kirimkan pesan untuk tim kami.' }}</h2>
                    <p class="text-muted">{{ $settings['details.description'] ?? 'Silakan tinggalkan pesan melalui formulir berikut.' }}</p>
                </div>
                <form class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="contactName" placeholder="Nama Anda">
                            <label for="contactName">Nama Anda</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="contactEmail" placeholder="Email Anda">
                            <label for="contactEmail">Email Anda</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="contactSubject" placeholder="Subjek">
                            <label for="contactSubject">Subjek</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea class="form-control" id="contactMessage" style="height: 150px" placeholder="Tulis pesan"></textarea>
                            <label for="contactMessage">Pesan</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100 py-3">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if(($settings['social.visible'] ?? '1') === '1')
<section id="social" class="container-fluid py-5">
    <div class="container py-5">
        <div class="text-center mb-4">
            <h2 class="mb-3">{{ $settings['social.heading'] ?? 'Ikuti Kami' }}</h2>
            @if(!empty($settings['social.description']))
                <p class="text-muted">{{ $settings['social.description'] }}</p>
            @endif
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            @forelse($visibleSocials as $item)
                @php $url = $item['url'] ?? '#'; @endphp
                <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-square border-2">
                    @if(!empty($item['icon']))
                        <i class="{{ $item['icon'] }}"></i>
                    @else
                        <i class="fa fa-link"></i>
                    @endif
                </a>
            @empty
                <span class="text-muted">Belum ada tautan media sosial yang ditampilkan.</span>
            @endforelse
        </div>
    </div>
</section>
@endif

@if(($settings['map.visible'] ?? '1') === '1' && !empty($mapEmbed))
<section id="map" class="container-fluid py-5 bg-light">
    <div class="container py-5">
        @if(!empty($settings['map.heading']))
            <div class="text-center mb-4">
                <h2 class="mb-0">{{ $settings['map.heading'] }}</h2>
            </div>
        @endif
        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm contact-map">
            @if(Str::contains($mapEmbed, ['<iframe', '<IFRAME']))
                {!! $mapEmbed !!}
            @else
                <iframe src="{{ $mapEmbed }}" allowfullscreen="" loading="lazy"></iframe>
            @endif
        </div>
    </div>
</section>
@endif

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

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

@once
<style>
    .contact-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .contact-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(15, 23, 43, 0.12);
    }
    .btn-square {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .contact-map iframe {
        border: 0;
        width: 100%;
        height: 100%;
    }
</style>
@endonce
</body>
</html>
