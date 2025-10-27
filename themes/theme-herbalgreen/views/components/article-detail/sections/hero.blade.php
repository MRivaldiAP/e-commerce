<section id="hero" class="page-hero" style="background-image:url('{{ $heroImage }}')">
    <div class="overlay{{ ($settings['hero.mask'] ?? '0') == '1' ? ' overlay-dark' : '' }}"></div>
    <div class="page-hero__content">
        <span class="tagline">{{ $settings['hero.title'] ?? 'Artikel' }}</span>
        <h1>{{ $article['title'] ?? 'Artikel' }}</h1>
    </div>
</section>
