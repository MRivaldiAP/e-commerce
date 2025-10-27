<section id="about" class="about spad">
    <div class="container">
        <div class="row about__row gy-4 gx-lg-5">
            <div class="col-lg-6">
                <div class="about__media">
                    <img src="{{ $aboutImage }}" alt="{{ $settings['about.heading'] ?? 'About Us' }}">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about__content">
                    <h2>{{ $settings['about.heading'] ?? 'About Us' }}</h2>
                    <p>{{ $settings['about.text'] ?? 'We provide quality products.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
