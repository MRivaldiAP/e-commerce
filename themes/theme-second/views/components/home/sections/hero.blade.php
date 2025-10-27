<section id="hero" class="hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="hero__item set-bg" data-setbg="{{ $heroBackground }}">
                    <div class="hero__text">
                        <span>{{ $settings['hero.tagline'] ?? 'FRUIT FRESH' }}</span>
                        <h2>{{ $settings['hero.heading'] ?? 'Vegetable 100% Organic' }}</h2>
                        <p>{{ $settings['hero.description'] ?? 'Free Pickup and Delivery Available' }}</p>
                        @if(!empty($settings['hero.button_label']))
                            <a href="{{ $settings['hero.button_link'] ?? '' }}" class="primary-btn">{{ $settings['hero.button_label'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
