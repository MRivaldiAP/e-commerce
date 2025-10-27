@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-restoran';
    $pageSettings = PageSetting::forPage('about');
    $settings = array_merge($pageSettings, $settings ?? []);
    $teamMembers = json_decode($settings['team.members'] ?? '[]', true);
    $advantages = json_decode($settings['advantages.items'] ?? '[]', true);

    if (! is_array($teamMembers)) {
        $teamMembers = [];
    }

    if (! is_array($advantages)) {
        $advantages = [];
    }

    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $activeSections = PageElements::activeSectionKeys($themeName, $settings);
    $heroActive = in_array('hero', $activeSections, true);
    $introActive = in_array('intro', $activeSections, true);
    $quoteActive = in_array('quote', $activeSections, true);
    $teamActive = in_array('team', $activeSections, true);
    $advantagesActive = in_array('advantages', $activeSections, true);

    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.background'] ?? null);
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');

    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }

    if ($heroBackground) {
        $heroStyle = $heroMaskEnabled
            ? "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroBackground}'); background-size: cover; background-position: center;"
            : "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9));'
            : 'background: var(--theme-accent);';
    }

    $heroSection = [
        'visible' => $heroActive && ($settings['hero.visible'] ?? '1') === '1',
        'classes' => $heroClasses,
        'style' => $heroStyle,
        'heading' => $settings['hero.heading'] ?? 'Tentang Kami',
        'text' => $settings['hero.text'] ?? null,
    ];

    $introSection = [
        'visible' => $introActive && ($settings['intro.visible'] ?? '1') === '1',
        'image' => ThemeMedia::url($settings['intro.image'] ?? null),
        'heading' => $settings['intro.heading'] ?? 'Cerita Kami',
        'description' => $settings['intro.description'] ?? 'Kami menyajikan pengalaman kuliner terbaik dengan bahan pilihan dan layanan penuh kehangatan.',
        'badge' => $settings['hero.heading'] ?? 'Tentang Kami',
    ];

    $quoteSection = [
        'visible' => $quoteActive && ($settings['quote.visible'] ?? '1') === '1' && ! empty($settings['quote.text']),
        'text' => $settings['quote.text'] ?? null,
        'author' => $settings['quote.author'] ?? null,
    ];

    $teamSection = [
        'visible' => $teamActive && ($settings['team.visible'] ?? '1') === '1' && count($teamMembers),
        'heading' => $settings['team.heading'] ?? 'Tim Kami',
        'description' => $settings['team.description'] ?? null,
        'members' => $teamMembers,
    ];

    $advantagesSection = [
        'visible' => $advantagesActive && ($settings['advantages.visible'] ?? '1') === '1' && count($advantages),
        'heading' => $settings['advantages.heading'] ?? 'Keunggulan Kami',
        'description' => $settings['advantages.description'] ?? null,
        'items' => $advantages,
    ];
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
    @include('themeRestoran::components.palette', ['theme' => $themeName])
    <style>
        .hero-header {background-size: cover; background-position: center;}
    </style>
</head>
<body>
<div class="container-xxl position-relative p-0">
    @include('themeRestoran::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @if($heroSection['visible'] ?? false)
        @include('themeRestoran::components.about.sections.hero', ['hero' => $heroSection])
    @endif
</div>

@if($introSection['visible'] ?? false)
    @include('themeRestoran::components.about.sections.intro', ['intro' => $introSection])
@endif

@if($quoteSection['visible'] ?? false)
    @include('themeRestoran::components.about.sections.quote', ['quote' => $quoteSection])
@endif

@if($teamSection['visible'] ?? false)
    @include('themeRestoran::components.about.sections.team', ['team' => $teamSection])
@endif

@if($advantagesSection['visible'] ?? false)
    @include('themeRestoran::components.about.sections.advantages', ['advantages' => $advantagesSection])
@endif

@include('themeRestoran::components.footer', ['footer' => $footerConfig])

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

@include('themeRestoran::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
