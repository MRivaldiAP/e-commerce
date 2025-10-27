<section class="breadcrumb-section set-bg" data-setbg="{{ $heroBackground }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['title'] ?? 'Produk Kami' }}</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <span>{{ $settings['title'] ?? 'Produk Kami' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
