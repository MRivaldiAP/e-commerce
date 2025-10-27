<div id="about" class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img class="img-fluid rounded w-100" src="{{ $aboutImage ? asset('storage/'.$aboutImage) : asset('storage/themes/theme-restoran/img/about-1.jpg') }}" alt="">
            </div>
            <div class="col-lg-6">
                <h5 class="section-title ff-secondary text-start text-primary fw-normal">About Us</h5>
                <h1 class="mb-4">{{ $settings['about.heading'] ?? 'Welcome to Restoran' }}</h1>
                <p class="mb-4">{{ $settings['about.text'] ?? 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit.' }}</p>
            </div>
        </div>
    </div>
</div>
