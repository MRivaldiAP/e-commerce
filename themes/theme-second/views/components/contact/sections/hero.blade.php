<section id="hero" class="breadcrumb-section set-bg" data-setbg="{{ $heroBackground }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.heading'] ?? 'Kontak Kami' }}</h2>
                    @if(!empty($settings['hero.description']))
                        <p>{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
