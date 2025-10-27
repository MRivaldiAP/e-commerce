<section id="quote" class="about-quote">
    <blockquote>
        <p>“{{ $settings['quote.text'] }}”</p>
        @if(!empty($settings['quote.author']))
            <cite>— {{ $settings['quote.author'] }}</cite>
        @endif
    </blockquote>
</section>
