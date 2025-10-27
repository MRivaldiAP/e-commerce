<div id="testimonials" class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="text-center">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Testimonial</h5>
            <h1 class="mb-5">Our Clients Say!!!</h1>
        </div>
        <div class="owl-carousel testimonial-carousel">
            @foreach($testimonials as $testimonial)
            <div class="testimonial-item bg-transparent border rounded p-4">
                <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
                <p>{{ $testimonial['text'] ?? '' }}</p>
                <div class="d-flex align-items-center">
                    @php $photo = $testimonial['photo'] ?? null; @endphp
                    <img class="img-fluid flex-shrink-0 rounded-circle" src="{{ $photo ? asset('storage/'.$photo) : asset('storage/themes/theme-restoran/img/testimonial-1.jpg') }}" style="width: 50px; height: 50px;">
                    <div class="ps-3">
                        <h5 class="mb-1">{{ $testimonial['name'] ?? '' }}</h5>
                        <small>{{ $testimonial['title'] ?? '' }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
