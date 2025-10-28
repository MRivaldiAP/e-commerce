@php
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('contact');
    $activeSections = \App\Support\PageElements::activeSectionKeys('contact', $themeName, $settings);
    $detailItems = json_decode($settings['details.items'] ?? '[]', true);
    if (! is_array($detailItems)) {
        $detailItems = [];
    }
    $socialItems = json_decode($settings['social.items'] ?? '[]', true);
    if (! is_array($socialItems)) {
        $socialItems = [];
    }
    $cartSummary = \App\Support\Cart::summary();
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $heroBackground = \App\Support\ThemeMedia::url($settings['hero.background'] ?? null);
    $heroStyle = $heroBackground ? "background-image:url('{$heroBackground}')" : 'background-color:#1b4332;';
    $visibleSocials = collect($socialItems)->filter(function ($item) {
        return ($item['visible'] ?? '1') != '0';
    })->values();
    $mapEmbed = $settings['map.embed'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - Herbal Green</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $themeName . '/theme.css') }}">
    <script src="{{ asset('themes/' . $themeName . '/theme.js') }}" defer></script>
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
    @include('themeHerbalGreen::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @foreach ($activeSections as $sectionKey)
        @switch($sectionKey)
            @case('hero')
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.contact.sections.hero', [
                    'settings' => $settings,
                    'heroStyle' => $heroStyle,
                ])
                @break

            @case('details')
                @includeWhen(($settings['details.visible'] ?? '1') == '1', 'themeHerbalGreen::components.contact.sections.details', [
                    'settings' => $settings,
                    'detailItems' => $detailItems,
                ])
                @break

            @case('social')
                @includeWhen(($settings['social.visible'] ?? '1') == '1', 'themeHerbalGreen::components.contact.sections.social', [
                    'settings' => $settings,
                    'socialItems' => $visibleSocials,
                ])
                @break

            @case('map')
                @includeWhen(($settings['map.visible'] ?? '1') == '1' && ! empty($mapEmbed), 'themeHerbalGreen::components.contact.sections.map', [
                    'settings' => $settings,
                    'mapEmbed' => $mapEmbed,
                ])
                @break
        @endswitch
    @endforeach

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
