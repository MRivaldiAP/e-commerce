@php
    use Illuminate\Support\Str;
    use App\Support\ThemeSectionLocator;

    $themeName = $theme ?? 'theme-herbalgreen';
    $settings = \App\Models\PageSetting::forPage('about', $themeName);
    $elements = \App\Support\PageElements::themeSections($themeName);
    $activeSections = \App\Support\PageElements::activeSectionKeys('about', $themeName, $settings);
    $teamMembers = json_decode($settings['team.members'] ?? '[]', true);
    if (! is_array($teamMembers)) {
        $teamMembers = [];
    }
    $advantages = json_decode($settings['advantages.items'] ?? '[]', true);
    if (! is_array($advantages)) {
        $advantages = [];
    }
    $navigation = \App\Support\LayoutSettings::navigation($themeName);
    $footerConfig = \App\Support\LayoutSettings::footer($themeName);
    $cartSummary = \App\Support\Cart::summary();

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Herbal Green</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $themeName . '/theme.css') }}">
    <script src="{{ asset('themes/' . $themeName . '/theme.js') }}" defer></script>
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
                @includeWhen(($settings['hero.visible'] ?? '1') == '1', 'themeHerbalGreen::components.about.sections.hero', [
                    'settings' => $settings,
                    'resolveMedia' => $resolveMedia,
                ])
                @break

            @case('intro')
                @includeWhen(($settings['intro.visible'] ?? '1') == '1', 'themeHerbalGreen::components.about.sections.intro', [
                    'settings' => $settings,
                    'resolveMedia' => $resolveMedia,
                ])
                @break

            @case('quote')
                @includeWhen(($settings['quote.visible'] ?? '1') == '1' && !empty($settings['quote.text']), 'themeHerbalGreen::components.about.sections.quote', [
                    'settings' => $settings,
                ])
                @break

            @case('team')
                @includeWhen(($settings['team.visible'] ?? '1') == '1' && count($teamMembers), 'themeHerbalGreen::components.about.sections.team', [
                    'settings' => $settings,
                    'teamMembers' => $teamMembers,
                    'resolveMedia' => $resolveMedia,
                ])
                @break

            @case('advantages')
                @includeWhen(($settings['advantages.visible'] ?? '1') == '1' && count($advantages), 'themeHerbalGreen::components.about.sections.advantages', [
                    'settings' => $settings,
                    'advantages' => $advantages,
                ])
                @break

            @default
                @php
                    $sectionInfo = $elements[$sectionKey] ?? null;
                    $viewName = $sectionInfo ? ThemeSectionLocator::resolve(
                        $themeName,
                        'themeHerbalGreen',
                        'about',
                        $sectionKey,
                        $sectionInfo['origins'] ?? []
                    ) : null;
                @endphp
                @continue(!$viewName)
                @include($viewName, [
                    'settings' => $settings,
                    'theme' => $themeName,
                ])
        @endswitch
    @endforeach

    @include('themeHerbalGreen::components.footer', ['footer' => $footerConfig])
    @include('themeHerbalGreen::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
