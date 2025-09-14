<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
    <link rel="stylesheet" href="{{ asset('ogani-master/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/style.css') }}" type="text/css">
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Models\Category;
    $settings = PageSetting::where('theme', 'theme-second')->where('page', 'product')->pluck('value', 'key')->toArray();
    $query = Product::query();
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
    $navLinks = [
        ['label' => 'Homepage', 'href' => url('/'), 'visible' => true],
        ['label' => 'Produk', 'href' => url('/produk'), 'visible' => true],
    ];
@endphp
{!! view()->file(base_path('themes/theme-second/views/components/nav-menu.blade.php'), ['links' => $navLinks])->render() !!}
<section class="breadcrumb-section set-bg" data-setbg="{{ !empty($settings['hero.image']) ? asset('storage/'.$settings['hero.image']) : asset('ogani-master/img/breadcrumb.jpg') }}">
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
            <div class="col-lg-3 col-md-5">
                <div class="sidebar">
                    <form method="GET" id="sidebar-form">
                        <div class="sidebar__item">
                            <h4>Pencarian</h4>
                            <div class="hero__search__form">
                                <input type="text" name="search" placeholder="Cari Produk..." value="{{ request('search') }}">
                                <button type="submit" class="site-btn">SEARCH</button>
                            </div>
                        </div>
                        <div class="sidebar__item">
                            <h4>Kategori</h4>
                            <ul>
                                <li class="{{ request('category') ? '' : 'active' }}"><a href="#" onclick="event.preventDefault();document.getElementById('category').value='';document.getElementById('sidebar-form').submit();">Semua</a></li>
                                @foreach($categories as $cat)
                                    <li class="{{ request('category')==$cat->slug ? 'active' : '' }}"><a href="#" onclick="event.preventDefault();document.getElementById('category').value='{{ $cat->slug }}';document.getElementById('sidebar-form').submit();">{{ $cat->name }}</a></li>
                                @endforeach
                            </ul>
                            <input type="hidden" id="category" name="category" value="{{ request('category') }}">
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
                        @php $img = $product->image_url ?? optional($product->images()->first())->path; @endphp
                        <div class="col-lg-4 col-md-6 col-sm-6">
                            <div class="product__item">
                                <div class="product__item__pic set-bg" data-setbg="{{ $img ? asset('storage/'.$img) : asset('ogani-master/img/product/product-1.jpg') }}">
                                    <ul class="product__item__pic__hover">
                                        <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                        <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                        <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                                    </ul>
                                </div>
                                <div class="product__item__text">
                                    <h6><a href="{{ url('products/'.$product->id) }}">{{ $product->name }}</a></h6>
                                    <h5>{{ $product->price_formatted ?? number_format($product->price,0,',','.') }}</h5>
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
{!! view()->file(base_path('themes/theme-second/views/components/footer.blade.php'), ['settings' => $settings])->render() !!}
<script src="{{ asset('ogani-master/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('ogani-master/js/mixitup.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/main.js') }}"></script>
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
</body>
</html>
