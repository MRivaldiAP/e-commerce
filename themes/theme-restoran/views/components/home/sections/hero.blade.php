<div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
    <div class="container my-5 py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                @if(!empty($settings['hero.tagline']))
                <span class="text-white">{{ $settings['hero.tagline'] }}</span>
                @endif
                <h1 class="display-3 text-white animated slideInLeft">{{ $settings['hero.heading'] ?? 'Enjoy Our Delicious Meal' }}</h1>
                <p class="text-white animated slideInLeft mb-4 pb-2">{{ $settings['hero.description'] ?? 'Tempor erat elitr rebum at clita.' }}</p>
                <a href="{{ $settings['hero.button_link'] ?? '#' }}" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">{{ $settings['hero.button_label'] ?? 'Book A Table' }}</a>
            </div>
            <div class="col-lg-6 text-center text-lg-end overflow-hidden position-relative">
                <img class="img-fluid main spin" src="{{ $heroSpinImage ?: asset('storage/themes/theme-restoran/img/hero.png') }}" alt="">
                @if(!empty($settings['hero.spin_text']))
                <span class="text-white position-absolute top-50 start-50 translate-middle spin-text">{{ $settings['hero.spin_text'] }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
