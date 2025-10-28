<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        .header {position: sticky; top: 0; z-index: 1000; background: #fff;}
        .contact__widget__item i {font-size: 32px; color: #7fad39;}
        .contact__social a {display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 50%; border: 1px solid #e8e8e8; transition: all .2s ease;}
        .contact__social a:hover {background: #7fad39; color: #fff; border-color: #7fad39;}
        .contact__map iframe {width: 100%; border: 0; min-height: 420px;}
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-second';
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

    $resolveMedia = function (?string $value) {
        if (! $value) {
            return null;
        }
        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    };

    $mapEmbed = $settings['map.embed'] ?? '';
    $heroBackground = $resolveMedia($settings['hero.background'] ?? null) ?: asset('storage/themes/theme-second/img/breadcrumb.jpg');

    $visibleSocials = collect($socialItems)->filter(function ($item) {
        return ($item['visible'] ?? '1') !== '0';
    });

    $formatLink = function (?string $value, ?string $link) {
        if (! $value) {
            return '';
        }
        if ($link) {
            return '<a href="' . e($link) . '" target="_blank" rel="noopener">' . e($value) . '</a>';
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '<a href="mailto:' . e($value) . '">' . e($value) . '</a>';
        }
        if (Str::startsWith($value, '+') || preg_match('/^[0-9\s()+-]+$/', $value)) {
            return '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $value)) . '">' . e($value) . '</a>';
        }
        return e($value);
    };
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="breadcrumb-section set-bg" data-setbg="{{ $heroBackground }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h2>
                    @if(!empty($settings['hero.description']))
                    <p>{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['details.visible'] ?? '1') == '1')
<section id="details" class="contact spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title contact__title">
                    <h2>{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h2>
                    @if(!empty($settings['details.description']))
                    <p>{{ $settings['details.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            @forelse($detailItems as $item)
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="contact__widget__item">
                    @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                    @else
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    @endif
                    @if(!empty($item['label']))
                    <h4>{{ $item['label'] }}</h4>
                    @endif
                    @if(!empty($item['value']))
                    <p>{!! $formatLink($item['value'], $item['link'] ?? null) !!}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-lg-12">
                <p class="text-center">Informasi kontak akan segera diperbarui.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endif

@if(($settings['social.visible'] ?? '1') == '1')
<section id="social" class="contact spad" style="padding-top:0">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-title contact__title">
                    <h2>{{ $settings['social.heading'] ?? 'Ikuti Kami' }}</h2>
                    @if(!empty($settings['social.description']))
                    <p>{{ $settings['social.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="contact__social text-center">
                    @forelse($visibleSocials as $item)
                        @php $url = $item['url'] ?? '#'; @endphp
                        <a href="{{ $url }}" target="_blank" rel="noopener">
                            @if(!empty($item['icon']))
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            @else
                            <i class="fa fa-link" aria-hidden="true"></i>
                            @endif
                        </a>
                    @empty
                        <span>Belum ada tautan media sosial yang ditampilkan.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['map.visible'] ?? '1') == '1' && !empty($mapEmbed))
<section id="map" class="contact-map">
    @if(!empty($settings['map.heading']))
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-title contact__title" style="margin-bottom: 1.5rem;">
                    <h2>{{ $settings['map.heading'] }}</h2>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="container">
        <div class="contact__map">
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

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>
<script>
    document.querySelectorAll('[data-setbg]').forEach(function(el){
        const bg = el.getAttribute('data-setbg');
        if(bg){ el.style.backgroundImage = `url(${bg})`; }
    });
</script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
