<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        .product__item__badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #e53935;
            color: #fff;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .product__item__text .price-original {
            display: block;
            font-size: 0.85rem;
            color: #9e9e9e;
            text-decoration: line-through;
        }

        .product__item__text .price-discount {
            display: block;
            color: #e65100;
            font-weight: 700;
            font-size: 1.05rem;
        }
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Models\Category;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    $themeName = $theme ?? 'theme-second';
    $settings = PageSetting::forPage('product');
    $query = Product::query()->with(['images', 'promotions']);
    if($s = request('search')){ $query->where('name','like',"%$s%"); }
    if($cat = request('category')){ $query->whereHas('categories', fn($q)=>$q->where('slug',$cat)); }
    if($min = request('minprice')){ $query->where('price', '>=', $min); }
    if($max = request('maxprice')){ $query->where('price', '<=', $max); }
    if($sort = request('sort')){
        if($sort==='price_asc') $query->orderBy('price');
        elseif($sort==='price_desc') $query->orderByDesc('price');
        elseif($sort==='sold_desc') $query->withCount('orderItems')->orderByDesc('order_items_count');
    }
    $products = $query->paginate(15)->withQueryString();
    $categories = Category::all();
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}
<section class="breadcrumb-section set-bg" data-setbg="{{ !empty($settings['hero.image']) ? asset('storage/'.$settings['hero.image']) : asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
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
<section class="product spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="hero__search">
                    <div class="hero__search__form">
                        <form method="GET">
                            <input type="text" name="search" placeholder="Cari Produk..." value="{{ request('search') }}">
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            <input type="hidden" name="minprice" value="{{ request('minprice') }}">
                            <input type="hidden" name="maxprice" value="{{ request('maxprice') }}">
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                            <button type="submit" class="site-btn">SEARCH</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-5">
                <div class="sidebar">
                    <form method="GET" id="sidebar-form">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" id="category" name="category" value="{{ request('category') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <div class="sidebar__item">
                            <h4>Kategori</h4>
                            <ul>
                                <li class="{{ request('category') ? '' : 'active' }}"><a href="#" onclick="event.preventDefault();document.getElementById('category').value='';document.getElementById('sidebar-form').submit();">Semua</a></li>
                                @foreach($categories as $cat)
                                    <li class="{{ request('category')==$cat->slug ? 'active' : '' }}"><a href="#" onclick="event.preventDefault();document.getElementById('category').value='{{ $cat->slug }}';document.getElementById('sidebar-form').submit();">{{ $cat->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="sidebar__item">
                            <h4>Harga</h4>
                            <div class="price-range-wrap">
                                <div id="price-range" class="price-range"></div>
                                <div class="range-slider">
                                    <div class="price-input">
                                        <input type="text" id="minamount" name="minprice" value="{{ request('minprice',0) }}">
                                        <input type="text" id="maxamount" name="maxprice" value="{{ request('maxprice',1000000) }}">
                                    </div>
                                    <button type="submit" class="site-btn mt-3">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-9 col-md-7">
                <div class="filter__item">
                    <div class="row">
                        <div class="col-lg-4 col-md-5">
                            <div class="filter__sort">
                                <span>Sort By</span>
                                <form method="GET" id="sort-form">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                    <input type="hidden" name="minprice" value="{{ request('minprice') }}">
                                    <input type="hidden" name="maxprice" value="{{ request('maxprice') }}">
                                    <select name="sort" onchange="document.getElementById('sort-form').submit()">
                                        <option value="">Default</option>
                                        <option value="price_asc" @selected(request('sort')=='price_asc')>Harga Terendah</option>
                                        <option value="price_desc" @selected(request('sort')=='price_desc')>Harga Tertinggi</option>
                                        <option value="sold_desc" @selected(request('sort')=='sold_desc')>Terjual Terbanyak</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-7">
                            <div class="filter__found">
                                <h6><span>{{ $products->total() }}</span> Produk ditemukan</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @foreach($products as $product)
                        @php
                            $imagePath = $product->image_url ?? optional($product->images->first())->path;
                            $promotion = $product->currentPromotion();
                            $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
                            $finalPrice = $product->final_price;
                        @endphp
                        <div class="col-lg-4 col-md-6 col-sm-6">
                            <div class="product__item">
                                <div class="product__item__pic set-bg" data-setbg="{{ $imagePath ? asset('storage/'.$imagePath) : asset('storage/themes/theme-second/img/product/product-1.jpg') }}">
                                    @if($hasPromo)
                                        <span class="product__item__badge">{{ $promotion->label }}</span>
                                    @endif
                                    <ul class="product__item__pic__hover">
                                        <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                        <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                        <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                                    </ul>
                                </div>
                                <div class="product__item__text">
                                    <h6><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h6>
                                    @if($hasPromo)
                                        <span class="price-original">Rp {{ number_format($product->price,0,',','.') }}</span>
                                        <span class="price-discount">Rp {{ number_format($finalPrice,0,',','.') }}</span>
                                    @else
                                        <span class="price-discount">Rp {{ number_format($finalPrice,0,',','.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="product__pagination">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>
<script>
    $(function(){
        var min = {{ request('minprice',0) }};
        var max = {{ request('maxprice',1000000) }};
        $("#price-range").slider({
            range: true,
            min: 0,
            max: 1000000,
            values: [min, max],
            slide: function(event, ui) {
                $("#minamount").val(ui.values[0]);
                $("#maxamount").val(ui.values[1]);
            }
        });
        $("#minamount").val($("#price-range").slider("values", 0));
        $("#maxamount").val($("#price-range").slider("values", 1));
    });
</script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
