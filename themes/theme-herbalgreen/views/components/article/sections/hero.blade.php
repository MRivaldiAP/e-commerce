<section id="hero" class="page-hero" style="background-image:url('{{ $heroImage }}')">
    <div class="overlay{{ ($settings['hero.mask'] ?? '0') == '1' ? ' overlay-dark' : '' }}"></div>
    <div class="page-hero__content">
        <span class="tagline">{{ $settings['hero.description'] ?? 'Kabar dan cerita terbaru' }}</span>
        <h1>{{ $settings['hero.heading'] ?? 'Artikel' }}</h1>
    </div>
</section>
