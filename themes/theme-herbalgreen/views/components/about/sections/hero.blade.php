<section id="hero" class="hero about-hero" @if(!empty($settings['hero.background'])) style="background-image:url('{{ $resolveMedia($settings['hero.background']) }}')" @endif>
    <div class="hero-content">
        <h1>{{ $settings['hero.heading'] ?? 'Tentang Kami' }}</h1>
        @if(!empty($settings['hero.text']))
            <p>{{ $settings['hero.text'] }}</p>
        @endif
    </div>
</section>
