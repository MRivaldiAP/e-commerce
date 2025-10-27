<section id="hero" class="hero" @if($heroImage) style="background-image:url('{{ $heroImage }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.tagline'] ?? 'Go Natural' }}</span>
        <h1>{{ $settings['hero.heading'] ?? 'The Best Time to Drink Tea' }}</h1>
        <p>{{ $settings['hero.description'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}</p>
        <a href="{{ $settings['hero.button_link'] ?? '#products' }}" class="cta">{{ $settings['hero.button_label'] ?? 'Shop Now' }}</a>
    </div>
</section>
