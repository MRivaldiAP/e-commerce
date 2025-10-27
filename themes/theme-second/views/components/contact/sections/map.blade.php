@php
    use Illuminate\Support\Str;

    $containsIframe = Str::contains($mapEmbed, ['<iframe', '<IFRAME']);
@endphp
<section id="map" class="contact-map">
    @if(!empty($settings['map.heading']))
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="section-title contact__title" style="margin-bottom: 1.5rem;">
                        <h2>{{ $settings['map.heading'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="container">
        <div class="contact__map">
            @if($containsIframe)
                {!! $mapEmbed !!}
            @else
                <iframe src="{{ $mapEmbed }}" allowfullscreen="" loading="lazy"></iframe>
            @endif
        </div>
    </div>
</section>
