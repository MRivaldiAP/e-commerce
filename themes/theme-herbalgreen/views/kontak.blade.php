<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - Herbal Green</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
    <style>
        .contact-hero {
            position: relative;
            padding: 6rem 1.5rem;
            background-size: cover;
            background-position: center;
            color: #fff;
            text-align: center;
        }
        .contact-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(18, 28, 23, 0.65);
        }
        .contact-hero__content {
            position: relative;
            max-width: 720px;
            margin: 0 auto;
        }
        .contact-hero__content h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            margin-bottom: 1rem;
        }
        .contact-details {
            padding: 4rem 1.5rem;
            background: #f8faf8;
        }
        .contact-details__header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .contact-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        .contact-card {
            background: #fff;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 10px 30px rgba(18, 28, 23, 0.08);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            height: 100%;
        }
        .contact-card__icon {
            font-size: 2rem;
            color: var(--color-primary, #2d6a4f);
        }
        .contact-card__label {
            font-weight: 600;
            font-size: 1.05rem;
        }
        .contact-card__value a {
            color: inherit;
            text-decoration: none;
            border-bottom: 1px dotted transparent;
        }
        .contact-card__value a:hover {
            border-bottom-color: currentColor;
        }
        .contact-social {
            padding: 4rem 1.5rem;
        }
        .contact-social__list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        .contact-social__item {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1.5rem;
            border-radius: 999px;
            border: 1px solid rgba(45, 106, 79, 0.2);
            transition: all 0.2s ease;
            background: #fff;
        }
        .contact-social__item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(18, 28, 23, 0.08);
        }
        .contact-map {
            padding: 0 0 4rem;
        }
        .contact-map__inner {
            max-width: 1040px;
            margin: 0 auto;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(18, 28, 23, 0.12);
        }
        .contact-map__frame iframe,
        .contact-map__frame {
            width: 100%;
            min-height: 420px;
            border: 0;
        }
        @media (max-width: 768px) {
            .contact-hero {
                padding: 4.5rem 1.25rem;
            }
            .contact-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-herbalgreen';
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
    $heroBackground = $resolveMedia($settings['hero.background'] ?? null);
    $heroStyle = $heroBackground ? "background-image:url('{$heroBackground}')" : 'background-color:#1b4332;';

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

    $visibleSocials = collect($socialItems)->filter(function ($item) {
        $visible = $item['visible'] ?? '1';
        return (string) $visible !== '0';
    });
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="contact-hero" style="{{ $heroStyle }}">
    <div class="contact-hero__content">
        <h1>{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h1>
        @if(!empty($settings['hero.description']))
        <p>{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</section>
@endif

@if(($settings['details.visible'] ?? '1') == '1')
<section id="details" class="contact-details">
    <div class="contact-details__header">
        <h2>{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h2>
        @if(!empty($settings['details.description']))
        <p>{{ $settings['details.description'] }}</p>
        @endif
    </div>
    <div class="contact-cards">
        @forelse($detailItems as $item)
            <article class="contact-card">
                @if(!empty($item['icon']))
                <span class="contact-card__icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                @endif
                @if(!empty($item['label']))
                <span class="contact-card__label">{{ $item['label'] }}</span>
                @endif
                @if(!empty($item['value']))
                <p class="contact-card__value">{!! $formatLink($item['value'], $item['link'] ?? null) !!}</p>
                @endif
            </article>
        @empty
            <p class="text-center">Informasi kontak akan segera hadir.</p>
        @endforelse
    </div>
</section>
@endif

@if(($settings['social.visible'] ?? '1') == '1')
<section id="social" class="contact-social">
    <div class="contact-details__header">
        <h2>{{ $settings['social.heading'] ?? 'Terhubung dengan Kami' }}</h2>
        @if(!empty($settings['social.description']))
        <p>{{ $settings['social.description'] }}</p>
        @endif
    </div>
    <div class="contact-social__list">
        @forelse($visibleSocials as $item)
            @php $url = $item['url'] ?? '#'; @endphp
            <a class="contact-social__item" href="{{ $url }}" target="_blank" rel="noopener">
                @if(!empty($item['icon']))
                <span><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                @endif
                <span>{{ $item['label'] ?? $url }}</span>
            </a>
        @empty
            <span>Belum ada tautan media sosial yang ditampilkan.</span>
        @endforelse
    </div>
</section>
@endif

@if(($settings['map.visible'] ?? '1') == '1' && !empty($mapEmbed))
<section id="map" class="contact-map">
    @if(!empty($settings['map.heading']))
    <div class="contact-details__header">
        <h2>{{ $settings['map.heading'] }}</h2>
    </div>
    @endif
    <div class="contact-map__inner">
        <div class="contact-map__frame">
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

{!! view()->file(base_path('themes/' . $theme . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $theme,
])->render() !!}
</body>
</html>
