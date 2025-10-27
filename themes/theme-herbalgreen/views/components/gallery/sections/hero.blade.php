@php
    $heroMask = ($settings['hero.mask'] ?? '0') === '1';
@endphp
<section id="hero" class="hero gallery-hero {{ $heroMask ? 'gallery-hero--mask' : '' }}" @if($heroBackground) style="background-image:url('{{ $heroBackground }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.heading'] ?? 'Galeri' }}</span>
        @if(!empty($settings['hero.description']))
            <p>{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</section>
