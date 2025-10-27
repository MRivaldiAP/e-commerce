<section id="testimonials" class="from-blog spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title from-blog__title">
                    <h2>Testimonials</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($testimonials as $testimonial)
                @php
                    $photo = $testimonial['photo'] ?? null;
                    $fallback = asset('storage/themes/' . ($theme ?? 'theme-second') . '/img/blog/blog-' . (($loop->iteration - 1) % 3 + 1) . '.jpg');
                    $photoUrl = $photo ? asset('storage/' . ltrim($photo, '/')) : $fallback;
                @endphp
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="blog__item">
                        <div class="blog__item__pic">
                            <img src="{{ $photoUrl }}" alt="{{ $testimonial['name'] ?? '' }}">
                        </div>
                        <div class="blog__item__text">
                            <ul>
                                <li><i class="fa fa-user"></i> {{ $testimonial['name'] ?? '' }}</li>
                            </ul>
                            <h5><a href="#">{{ $testimonial['title'] ?? '' }}</a></h5>
                            <p>{{ $testimonial['text'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
