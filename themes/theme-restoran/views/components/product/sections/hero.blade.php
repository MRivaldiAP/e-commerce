@if($hero['visible'] ?? false)
<div id="hero" class="{{ $hero['classes'] ?? '' }}" style="{{ $hero['style'] ?? '' }}">
    <div class="container text-center my-5 pt-5 pb-4">
        <h1 class="display-3 text-white mb-3">{{ $hero['title'] ?? 'Produk Kami' }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center text-uppercase">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">{{ $hero['title'] ?? 'Produk Kami' }}</li>
            </ol>
        </nav>
    </div>
</div>
@endif
