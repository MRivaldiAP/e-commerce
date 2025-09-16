<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Detail Produk</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;

    $settings = PageSetting::where('theme', 'theme-restoran')->where('page', 'product-detail')->pluck('value','key')->toArray();
    $navLinks = [
        ['label' => 'Homepage', 'href' => url('/'), 'visible' => true],
        ['label' => 'Produk', 'href' => url('/produk'), 'visible' => true],
    ];

    $images = $product->images ?? collect();
    $imageSources = $images->pluck('path')->filter()->map(fn($path) => asset('storage/'.$path))->values();
    if ($imageSources->isEmpty()) {
        $imageSources = collect(['https://via.placeholder.com/600x400?text=No+Image']);
    }

    $comments = $product->comments ?? collect();

    $recommendationsQuery = Product::query()->where('id', '!=', $product->id);
    if ($product->categories && $product->categories->count()) {
        $recommendationsQuery->whereHas('categories', fn($q) => $q->whereIn('categories.id', $product->categories->pluck('id')));
    }
    $recommendations = $recommendationsQuery->with('images')->take(5)->get();
    if ($recommendations->count() < 5) {
        $fallback = Product::where('id', '!=', $product->id)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with('images')
            ->take(5 - $recommendations->count())
            ->get();
        $recommendations = $recommendations->concat($fallback);
    }
@endphp
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/theme-restoran/views/components/nav-menu.blade.php'), ['links' => $navLinks])->render() !!}
    @if(($settings['hero.visible'] ?? '1') == '1')
    <div class="container-xxl py-5 bg-dark hero-header mb-5" @if(!empty($settings['hero.image'])) style="background-image:url('{{ asset('storage/'.$settings['hero.image']) }}'); background-size:cover; background-position:center;" @endif>
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $settings['hero.title'] ?? $product->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/produk') }}">Produk</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>
    @endif
</div>

<div class="container py-5">
    <div class="row g-5 align-items-start">
        <div class="col-lg-6">
            <div class="position-relative overflow-hidden rounded">
                <img src="{{ $imageSources->first() }}" alt="{{ $product->name }}" class="img-fluid w-100" id="mainProductImage">
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                @foreach($imageSources as $index => $src)
                    <img src="{{ $src }}" data-full="{{ $src }}" class="img-thumbnail product-thumb {{ $index === 0 ? 'border-primary' : '' }}" style="width: 80px; height: 80px; object-fit: cover; cursor:pointer;" alt="{{ $product->name }} thumbnail {{ $index + 1 }}">
                @endforeach
            </div>
        </div>
        <div class="col-lg-6">
            <h2 class="mb-3">{{ $product->name }}</h2>
            <h3 class="text-primary mb-4">Rp {{ number_format($product->price, 0, ',', '.') }}</h3>
            <p class="mb-4">{{ $product->short_description ?? 'Nikmati cita rasa terbaik dari produk pilihan kami.' }}</p>
            <div class="d-flex align-items-center mb-3" id="quantityControl">
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" type="button" data-action="decrease">-</button>
                    <input type="number" class="form-control text-center" value="1" min="1" id="quantityInput">
                    <button class="btn btn-outline-secondary" type="button" data-action="increase">+</button>
                </div>
            </div>
            <div class="mb-4">
                <button class="btn btn-primary py-2 px-4 me-2" id="addToCartButton"><i class="bi bi-cart"></i> Masukkan ke Keranjang</button>
                <button class="btn btn-outline-primary py-2 px-4"><i class="bi bi-heart"></i></button>
            </div>
            <div class="mt-4">
                <h5 class="mb-3">Deskripsi Produk</h5>
                <p>{!! $product->description ? nl2br(e($product->description)) : 'Belum ada deskripsi produk.' !!}</p>
            </div>
        </div>
    </div>
</div>

@if(($settings['comments.visible'] ?? '1') == '1')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h3 class="mb-4 text-center">{{ $settings['comments.heading'] ?? 'Komentar Pelanggan' }}</h3>
            @if($comments->isEmpty())
                <p class="text-center text-muted">Belum ada komentar untuk produk ini.</p>
            @else
                @foreach($comments as $comment)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</h5>
                                <small class="text-muted">{{ optional($comment->created_at)->format('d M Y') }}</small>
                            </div>
                            <p class="card-text mb-0">{{ $comment->content }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif

@if(($settings['recommendations.visible'] ?? '1') == '1' && $recommendations->count())
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title ff-secondary text-center text-primary fw-normal">{{ $settings['recommendations.heading'] ?? 'Produk Serupa' }}</h2>
    </div>
    <div class="row g-4">
        @foreach($recommendations as $item)
            @php $img = optional($item->images->first())->path; @endphp
            <div class="col-lg-6">
                <div class="d-flex align-items-center">
                    <img class="flex-shrink-0 img-fluid rounded" src="{{ $img ? asset('storage/'.$img) : asset('storage/themes/theme-restoran/img/menu-1.jpg') }}" alt="{{ $item->name }}" style="width: 80px;">
                    <div class="w-100 d-flex flex-column text-start ps-4">
                        <h5 class="d-flex justify-content-between border-bottom pb-2">
                            <span>{{ $item->name }}</span>
                            <span class="text-primary">{{ $item->price_formatted ?? number_format($item->price,0,',','.') }}</span>
                        </h5>
                        <small class="fst-italic">{{ \Illuminate\Support\Str::limit($item->short_description ?? $item->description, 80) }}</small>
                        <a href="{{ route('products.show', $item) }}" class="btn btn-sm btn-primary mt-2 align-self-start">Detail</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{!! view()->file(base_path('themes/theme-restoran/views/components/footer.blade.php'), ['settings' => $settings])->render() !!}

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const thumbs = document.querySelectorAll('.product-thumb');
        const mainImage = document.getElementById('mainProductImage');
        thumbs.forEach(function(thumb){
            thumb.addEventListener('click', function(){
                thumbs.forEach(t => t.classList.remove('border-primary'));
                this.classList.add('border-primary');
                const full = this.getAttribute('data-full');
                if(full){
                    mainImage.src = full;
                }
            });
        });

        const control = document.getElementById('quantityControl');
        const input = document.getElementById('quantityInput');
        if(control && input){
            control.addEventListener('click', function(event){
                const button = event.target.closest('button[data-action]');
                if(!button) return;
                const action = button.getAttribute('data-action');
                let current = parseInt(input.value || '1', 10);
                if(action === 'increase') current += 1;
                if(action === 'decrease') current = Math.max(1, current - 1);
                input.value = current;
            });
        }

        const addToCart = document.getElementById('addToCartButton');
        if(addToCart){
            addToCart.addEventListener('click', function(){
                alert('Produk ditambahkan ke keranjang sebanyak ' + (document.getElementById('quantityInput')?.value || 1) + ' pcs.');
            });
        }
    });
</script>
</body>
</html>
