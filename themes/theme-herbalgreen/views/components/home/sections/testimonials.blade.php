<section id="testimonials" class="testimonials">
    <h2>Testimonials</h2>
    @foreach($testimonials as $t)
        <div class="testimonial">
            <p>"{{ $t['text'] ?? '' }}"</p>
            <span>- {{ $t['title'] ?? '' }} {{ $t['name'] ?? '' }}</span>
        </div>
    @endforeach
</section>
