<div id="map" class="container-xxl py-5">
    <div class="container">
        @if(!empty($settings['map.heading']))
        <div class="text-center mb-4">
            <h5 class="section-title ff-secondary text-primary fw-normal">{{ $settings['map.heading'] }}</h5>
        </div>
        @endif
        <div class="contact-map">
            @if(\Illuminate\Support\Str::contains($mapEmbed, ['<iframe', '<IFRAME']))
                {!! $mapEmbed !!}
            @else
                <iframe src="{{ $mapEmbed }}" allowfullscreen="" loading="lazy"></iframe>
            @endif
        </div>
    </div>
</div>
