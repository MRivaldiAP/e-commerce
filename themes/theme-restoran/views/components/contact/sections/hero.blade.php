<div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
    <div class="container text-center my-5 pt-5 pb-4">
        <h1 class="display-4 text-white mb-3">{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h1>
        @if(!empty($settings['hero.description']))
        <p class="text-white-50 mb-0">{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</div>
