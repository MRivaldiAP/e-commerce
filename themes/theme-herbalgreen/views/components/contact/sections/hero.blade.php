<section id="hero" class="contact-hero" style="{{ $heroStyle }}">
    <div class="contact-hero__content">
        <h1>{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h1>
        @if(!empty($settings['hero.description']))
            <p>{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</section>
