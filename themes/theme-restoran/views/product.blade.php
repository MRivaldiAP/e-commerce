@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Produk</title>
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
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/palette.blade.php'), ['theme' => $themeName])->render() !!}
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Models\Category;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    $settings = PageSetting::forPage('product');
    $query = Product::query();
    if($s = request('search')){ $query->where('name','like',"%$s%"); }
    if($cat = request('category')){ $query->whereHas('categories', fn($q)=>$q->where('slug',$cat)); }
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
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null);
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    if ($heroBackground) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroBackground}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
        }
    } elseif (! $heroMaskEnabled) {
        $heroStyle = 'background-image: none;';
    }
@endphp
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    <div class="{{ $heroClasses }}" style="{{ $heroStyle }}">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $settings['title'] ?? 'Produk Kami' }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">{{ $settings['title'] ?? 'Produk Kami' }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<div class="container py-5">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari Produk" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('category')==$cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="">Urutkan</option>
                <option value="price_asc" @selected(request('sort')=='price_asc')>Harga Terendah</option>
                <option value="price_desc" @selected(request('sort')=='price_desc')>Harga Tertinggi</option>
                <option value="sold_desc" @selected(request('sort')=='sold_desc')>Terjual Terbanyak</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
    <div class="row g-4">
        @foreach($products as $product)
        @php $img = $product->image_url ?? optional($product->images()->first())->path; @endphp
        <div class="col-lg-6">
            <div class="d-flex align-items-center">
                <img class="flex-shrink-0 img-fluid rounded" src="{{ $img ? asset('storage/'.$img) : asset('storage/themes/theme-restoran/img/menu-1.jpg') }}" alt="{{ $product->name }}" style="width: 80px;">
                <div class="w-100 d-flex flex-column text-start ps-4">
                    <h5 class="d-flex justify-content-between border-bottom pb-2">
                        <span>{{ $product->name }}</span>
                        <span class="text-primary">{{ $product->price_formatted ?? number_format($product->price,0,',','.') }}</span>
                    </h5>
                    <small class="fst-italic">{{ $product->description }}</small>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-primary mt-2 align-self-start">Detail</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
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

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
