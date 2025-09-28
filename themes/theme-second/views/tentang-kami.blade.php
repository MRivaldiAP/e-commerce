<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami</title>
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
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-second';
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

    $resolveMedia = function (?string $value) {
        if (! $value) {
            return null;
        }
        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
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
<section id="hero" class="breadcrumb-section set-bg" data-setbg="{{ !empty($settings['hero.background']) ? $resolveMedia($settings['hero.background']) : asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.heading'] ?? 'Tentang Kami' }}</h2>
                    @if(!empty($settings['hero.text']))
                    <p>{{ $settings['hero.text'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['intro.visible'] ?? '1') == '1')
<section id="intro" class="about spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="about__pic">
                    @php $image = $resolveMedia($settings['intro.image'] ?? null); @endphp
                    <img src="{{ $image ?: asset('storage/themes/theme-second/img/about/about-1.jpg') }}" alt="{{ $settings['intro.heading'] ?? 'Tentang Kami' }}">
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="about__text">
                    <h2>{{ $settings['intro.heading'] ?? 'Cerita Kami' }}</h2>
                    <p>{{ $settings['intro.description'] ?? 'Kami berkomitmen menghadirkan produk segar dan layanan terbaik untuk keluarga Indonesia.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['quote.visible'] ?? '1') == '1' && !empty($settings['quote.text']))
<section id="quote" class="testimonial spad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="testimonial__item text-center">
                    <div class="testimonial__text">
                        <p>“{{ $settings['quote.text'] }}”</p>
                        @if(!empty($settings['quote.author']))
                        <h5>{{ $settings['quote.author'] }}</h5>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['team.visible'] ?? '1') == '1' && count($teamMembers))
<section id="team" class="team spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2>{{ $settings['team.heading'] ?? 'Tim Kami' }}</h2>
                    @if(!empty($settings['team.description']))
                    <p>{{ $settings['team.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($teamMembers as $index => $member)
            @php $photo = $resolveMedia($member['photo'] ?? null); @endphp
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="team__item">
                    <div class="team__item__pic">
                        <img src="{{ $photo ?: asset('storage/themes/theme-second/img/team/team-' . (($index % 4) + 1) . '.jpg') }}" alt="{{ $member['name'] ?? '' }}">
                    </div>
                    <div class="team__item__text">
                        <h5>{{ $member['name'] ?? '' }}</h5>
                        <span>{{ $member['title'] ?? '' }}</span>
                        @if(!empty($member['description']))
                        <p>{{ $member['description'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($settings['advantages.visible'] ?? '1') == '1' && count($advantages))
<section id="advantages" class="services spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2>{{ $settings['advantages.heading'] ?? 'Keunggulan Kami' }}</h2>
                    @if(!empty($settings['advantages.description']))
                    <p>{{ $settings['advantages.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($advantages as $advantage)
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="services__item">
                    @if(!empty($advantage['icon']))
                    <span class="icon"><i class="{{ $advantage['icon'] }}" aria-hidden="true"></i></span>
                    @endif
                    <h5>{{ $advantage['title'] ?? '' }}</h5>
                    <p>{{ $advantage['text'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
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
</body>
</html>
