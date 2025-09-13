<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
    <a href="{{ url('/') }}" class="navbar-brand p-0">
        <h1 class="text-primary m-0"><i class="fa fa-utensils me-3"></i>Restoran</h1>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0 pe-4">
            @foreach($links as $link)
                @if($link['visible'])
                    <a href="{{ $link['href'] }}" class="nav-item nav-link">{{ $link['label'] }}</a>
                @endif
            @endforeach
        </div>
        <a href="#" class="btn btn-primary py-2 px-4">Book A Table</a>
    </div>
</nav>
