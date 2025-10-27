<section id="services" class="services spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2>{{ $settings['services.heading'] ?? 'Our Services' }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($services as $service)
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_check"></span>
                        <h4>{{ $service['title'] ?? '' }}</h4>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
