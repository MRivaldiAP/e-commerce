<section id="hero" class="breadcrumb-section set-bg {{ $heroMasked ? 'breadcrumb-section--mask' : '' }}" data-setbg="{{ $heroBackground }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $article['title'] ?? ($settings['hero.title'] ?? 'Artikel') }}</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <a href="{{ route('articles.index') }}">Artikel</a>
                        <span>{{ $settings['hero.title'] ?? ($article['title'] ?? 'Detail Artikel') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@once
    <style>
        .breadcrumb-section--mask::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
        }
    </style>
@endonce
