<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Herbal Green</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = PageSetting::forPage('about', $themeName);
    $teamMembers = json_decode($settings['team.members'] ?? '[]', true);
    $advantages = json_decode($settings['advantages.items'] ?? '[]', true);
    if (!is_array($teamMembers)) {
        $teamMembers = [];
    }
    if (!is_array($advantages)) {
        $advantages = [];
    }
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

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
<section id="hero" class="hero about-hero" @if(!empty($settings['hero.background'])) style="background-image:url('{{ $resolveMedia($settings['hero.background']) }}')" @endif>
    <div class="hero-content">
        <h1>{{ $settings['hero.heading'] ?? 'Tentang Kami' }}</h1>
        @if(!empty($settings['hero.text']))
        <p>{{ $settings['hero.text'] }}</p>
        @endif
    </div>
</section>
@endif

@if(($settings['intro.visible'] ?? '1') == '1')
<section id="intro" class="about-intro">
    <div class="about-intro__grid">
        <div class="about-intro__image">
            @php $image = $resolveMedia($settings['intro.image'] ?? null); @endphp
            @if($image)
                <img src="{{ $image }}" alt="{{ $settings['intro.heading'] ?? 'Tentang Kami' }}">
            @endif
        </div>
        <div class="about-intro__content">
            <h2>{{ $settings['intro.heading'] ?? 'Perjalanan Kami' }}</h2>
            <p>{{ $settings['intro.description'] ?? 'Kami percaya pada kekuatan alam untuk menghadirkan kehidupan yang lebih sehat dan seimbang.' }}</p>
        </div>
    </div>
</section>
@endif

@if(($settings['quote.visible'] ?? '1') == '1' && !empty($settings['quote.text']))
<section id="quote" class="about-quote">
    <blockquote>
        <p>“{{ $settings['quote.text'] }}”</p>
        @if(!empty($settings['quote.author']))
        <cite>— {{ $settings['quote.author'] }}</cite>
        @endif
    </blockquote>
</section>
@endif

@if(($settings['team.visible'] ?? '1') == '1' && count($teamMembers))
<section id="team" class="about-team">
    <div class="section-header">
        <h2>{{ $settings['team.heading'] ?? 'Tim Kami' }}</h2>
        @if(!empty($settings['team.description']))
        <p>{{ $settings['team.description'] }}</p>
        @endif
    </div>
    <div class="about-team__grid">
        @foreach($teamMembers as $member)
            <div class="about-team__card">
                @php $photo = $resolveMedia($member['photo'] ?? null); @endphp
                @if($photo)
                <img src="{{ $photo }}" alt="{{ $member['name'] ?? '' }}" class="about-team__photo">
                @endif
                <h3>{{ $member['name'] ?? '' }}</h3>
                <span class="about-team__role">{{ $member['title'] ?? '' }}</span>
                @if(!empty($member['description']))
                <p>{{ $member['description'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
@endif

@if(($settings['advantages.visible'] ?? '1') == '1' && count($advantages))
<section id="advantages" class="about-advantages">
    <div class="section-header">
        <h2>{{ $settings['advantages.heading'] ?? 'Keunggulan Kami' }}</h2>
        @if(!empty($settings['advantages.description']))
        <p>{{ $settings['advantages.description'] }}</p>
        @endif
    </div>
    <div class="about-advantages__grid">
        @foreach($advantages as $advantage)
            <div class="about-advantages__item">
                @if(!empty($advantage['icon']))
                <span class="about-advantages__icon"><i class="{{ $advantage['icon'] }}"></i></span>
                @endif
                <h3>{{ $advantage['title'] ?? '' }}</h3>
                <p>{{ $advantage['text'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
</body>
</html>
