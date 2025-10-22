@php
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-restoran';
    $variation = LayoutSettings::variation($themeName);
    $colors = $variation['colors'] ?? [];

    $primary = $colors['primary'] ?? '#FEA116';
    $primaryRgb = $colors['primary_rgb'] ?? '254, 161, 22';
    $accent = $colors['accent'] ?? '#0F172B';
    $accentRgb = $colors['accent_rgb'] ?? '15, 23, 43';
    $light = $colors['light'] ?? '#FFFFFF';
    $lightRgb = $colors['light_rgb'] ?? '255, 255, 255';
    $background = $colors['background'] ?? '#FFFFFF';
    $text = $colors['text'] ?? '#212529';
    $contrast = $colors['contrast'] ?? '#FFFFFF';
@endphp
<style>
    :root {
        --bs-primary: {{ $primary }};
        --bs-primary-rgb: {{ $primaryRgb }};
        --bs-dark: {{ $accent }};
        --bs-dark-rgb: {{ $accentRgb }};
        --bs-light: {{ $light }};
        --bs-light-rgb: {{ $lightRgb }};
        --bs-body-bg: {{ $background }};
        --bs-body-color: {{ $text }};
        --primary: {{ $primary }};
        --light: {{ $light }};
        --dark: {{ $accent }};
        --theme-primary: {{ $primary }};
        --theme-primary-rgb: {{ $primaryRgb }};
        --theme-accent: {{ $accent }};
        --theme-accent-rgb: {{ $accentRgb }};
        --theme-accent-contrast: {{ $contrast }};
        --theme-surface: {{ $background }};
        --theme-on-surface: {{ $text }};
    }

    .bg-dark {
        background-color: var(--theme-accent) !important;
    }

    .bg-dark.hero-header {
        background: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9));
        background-size: cover;
        background-position: center;
    }

    .hero-header.hero-no-mask {
        background: var(--theme-accent);
    }

    .bg-dark .btn-outline-light {
        color: var(--theme-accent-contrast);
        border-color: rgba(var(--theme-accent-rgb), 0.35);
    }

    .bg-dark .btn-outline-light:hover {
        background-color: var(--theme-accent-contrast);
        color: var(--theme-accent);
        border-color: var(--theme-accent-contrast);
    }
</style>
