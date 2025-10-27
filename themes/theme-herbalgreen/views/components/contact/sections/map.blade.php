@php
    use Illuminate\Support\Str;
@endphp
<section id="map" class="contact-map">
    @if(!empty($settings['map.heading']))
        <div class="contact-details__header">
            <h2>{{ $settings['map.heading'] }}</h2>
        </div>
    @endif
    <div class="contact-map__inner">
        <div class="contact-map__frame">
            @if(Str::contains($mapEmbed, ['<iframe', '<IFRAME']))
                {!! $mapEmbed !!}
            @else
                <iframe src="{{ $mapEmbed }}" allowfullscreen="" loading="lazy"></iframe>
            @endif
        </div>
    </div>
</section>
