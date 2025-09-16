<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;

    $settings = PageSetting::where('theme', 'theme-second')->where('page', 'product-detail')->pluck('value', 'key')->toArray();
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
{!! view()->file(base_path('themes/theme-second/views/components/nav-menu.blade.php'), ['links' => $navLinks])->render() !!}

<section class="breadcrumb-section set-bg" data-setbg="{{ !empty($settings['hero.image']) ? asset('storage/'.$settings['hero.image']) : asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.title'] ?? $product->name }}</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <a href="{{ url('/produk') }}">Produk</a>
                        <span>{{ $product->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="product-details spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="product__details__pic">
                    <div class="product__details__pic__item">
                        <img class="product__details__pic__item--large" src="{{ $imageSources->first() }}" alt="{{ $product->name }}" id="mainProductImage">
                    </div>
                    <div class="product__details__pic__slider owl-carousel">
                        @foreach($imageSources as $src)
                            <img data-imgbigurl="{{ $src }}" src="{{ $src }}" alt="{{ $product->name }} thumbnail">
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="product__details__text">
                    <h3>{{ $product->name }}</h3>
                    <div class="product__details__price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    <p>{{ $product->short_description ?? 'Produk pilihan terbaik untuk memenuhi kebutuhan Anda setiap hari.' }}</p>
                    <div class="product__details__quantity">
                        <div class="quantity">
                            <div class="pro-qty">
                                <input type="text" value="1" id="quantityInput">
                            </div>
                        </div>
                    </div>
                    <a href="#" class="primary-btn" id="addToCartButton">MASUKKAN KE KERANJANG</a>
                    <a href="#" class="heart-icon"><span class="icon_heart_alt"></span></a>
                    <ul>
                        <li><b>Ketersediaan</b> <span>{{ $product->stock > 0 ? 'Stok Tersedia' : 'Stok Habis' }}</span></li>
                        <li><b>Berat</b> <span>{{ $product->weight ? $product->weight.' kg' : '-' }}</span></li>
                        <li><b>Bagikan</b>
                            <div class="share">
                                <a href="#"><i class="fa fa-facebook"></i></a>
                                <a href="#"><i class="fa fa-twitter"></i></a>
                                <a href="#"><i class="fa fa-instagram"></i></a>
                                <a href="#"><i class="fa fa-pinterest"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="product__details__tab">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab" aria-selected="true">Deskripsi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab" aria-selected="false">Informasi</a>
                        </li>
                        @if(($settings['comments.visible'] ?? '1') == '1')
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab" aria-selected="false">Komentar <span>({{ $comments->count() }})</span></a>
                        </li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tabs-1" role="tabpanel">
                            <div class="product__details__tab__desc">
                                {!! $product->description ? nl2br(e($product->description)) : '<p>Belum ada deskripsi produk.</p>' !!}
                            </div>
                        </div>
                        <div class="tab-pane" id="tabs-2" role="tabpanel">
                            <div class="product__details__tab__desc">
                                <ul class="list-unstyled">
                                    <li><strong>Kategori:</strong> {{ $product->categories->pluck('name')->join(', ') ?: '-' }}</li>
                                    <li><strong>Merek:</strong> {{ $product->brand?->name ?? '-' }}</li>
                                    <li><strong>SKU:</strong> {{ $product->sku ?? '-' }}</li>
                                    <li><strong>Dimensi:</strong> {{ $product->length }} x {{ $product->width }} x {{ $product->height }}</li>
                                </ul>
                            </div>
                        </div>
                        @if(($settings['comments.visible'] ?? '1') == '1')
                        <div class="tab-pane" id="tabs-3" role="tabpanel">
                            <div class="product__details__tab__desc">
                                @if($comments->isEmpty())
                                    <p>Belum ada komentar.</p>
                                @else
                                    @foreach($comments as $comment)
                                        <div class="mb-4">
                                            <h6 class="mb-1">{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</h6>
                                            <small class="text-muted d-block mb-2">{{ optional($comment->created_at)->format('d M Y') }}</small>
                                            <p class="mb-0">{{ $comment->content }}</p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(($settings['recommendations.visible'] ?? '1') == '1' && $recommendations->count())
<section class="related-product">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title related__product__title">
                    <h2>{{ $settings['recommendations.heading'] ?? 'Produk Serupa' }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($recommendations as $item)
                @php $img = optional($item->images->first())->path; @endphp
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product__item">
                        <div class="product__item__pic set-bg" data-setbg="{{ $img ? asset('storage/'.$img) : asset('storage/themes/theme-second/img/product/product-1.jpg') }}">
                            <ul class="product__item__pic__hover">
                                <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                            </ul>
                        </div>
                        <div class="product__item__text">
                            <h6><a href="{{ route('products.show', $item) }}">{{ $item->name }}</a></h6>
                            <h5>{{ $item->price_formatted ?? number_format($item->price,0,',','.') }}</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{!! view()->file(base_path('themes/theme-second/views/components/footer.blade.php'), ['settings' => $settings])->render() !!}

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const addToCart = document.getElementById('addToCartButton');
        if(addToCart){
            addToCart.addEventListener('click', function(event){
                event.preventDefault();
                alert('Produk ditambahkan ke keranjang sebanyak ' + (document.getElementById('quantityInput')?.value || 1) + ' pcs.');
            });
        }
    });
</script>
</body>
</html>
