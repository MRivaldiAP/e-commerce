<div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
    <div class="container text-center my-5 pt-5 pb-4">
        <h1 class="display-3 text-white mb-3">{{ $heroHeading }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center text-uppercase">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">{{ $heroHeading }}</li>
            </ol>
        </nav>
        @if(!empty($heroDescription))
            <p class="text-white-50 mt-3">{{ $heroDescription }}</p>
        @endif
    </div>
</div>
