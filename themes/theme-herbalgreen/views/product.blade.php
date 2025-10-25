<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
    <style>
        .product-card {
            position: relative;
            overflow: hidden;
        }

        .product-card .promo-label {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #e53935;
            color: #fff;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 600;
        }

        .product-card .price-original {
            display: block;
            text-decoration: line-through;
            color: #9e9e9e;
            font-size: 0.9rem;
        }

        .product-card .price-current {
            display: block;
            color: #2e7d32;
            font-weight: 700;
            font-size: 1.1rem;
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
    $settings = PageSetting::forPage('product');
    $query = Product::query()->with(['images', 'promotions']);
    if($s = request('search')){ $query->where('name', 'like', "%$s%"); }
    if($cat = request('category')){ $query->whereHas('categories', fn($q) => $q->where('slug', $cat)); }
    if($sort = request('sort')){
        if($sort === 'price_asc') $query->orderBy('price');
        elseif($sort === 'price_desc') $query->orderByDesc('price');
        elseif($sort === 'sold_desc') $query->withCount('orderItems')->orderByDesc('order_items_count');
    }
    $products = $query->paginate(15)->withQueryString();
    $categories = Category::all();
    $navigation = LayoutSettings::navigation($theme);
    $footerConfig = LayoutSettings::footer($theme);
    $cartSummary = Cart::summary();
@endphp
{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}
@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="hero" @if(!empty($settings['hero.image'])) style="background-image:url('{{ asset('storage/'.$settings['hero.image']) }}')" @endif>
    <div class="hero-content">
        <h1>{{ $settings['title'] ?? 'Produk Kami' }}</h1>
    </div>
</section>
@endif
<section class="product-search">
    <form method="GET">
        <input type="text" name="search" placeholder="Cari Produk..." value="{{ request('search') }}">
        <select name="category">
            <option value="">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category')==$category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="sort">
            <option value="">Urutkan</option>
            <option value="price_asc" @selected(request('sort')=='price_asc')>Harga Terendah</option>
            <option value="price_desc" @selected(request('sort')=='price_desc')>Harga Tertinggi</option>
            <option value="sold_desc" @selected(request('sort')=='sold_desc')>Terjual Terbanyak</option>
        </select>
        <button type="submit">Filter</button>
    </form>
</section>
<section id="products" class="products">
    <div class="product-grid">
        @foreach($products as $product)
            @php
                $imagePath = optional($product->images->first())->path;
                $promotion = $product->currentPromotion();
                $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
                $finalPrice = $product->final_price;
            @endphp
            <div class="product-card">
                <img src="{{ $imagePath ? asset('storage/'.$imagePath) : 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}">
                @if($hasPromo)
                    <span class="promo-label">{{ $promotion->label }}</span>
                @endif
                <h3>{{ $product->name }}</h3>
                @if($hasPromo)
                    <span class="price-original">Rp {{ number_format($product->price,0,',','.') }}</span>
                    <span class="price-current">Rp {{ number_format($finalPrice,0,',','.') }}</span>
                @else
                    <span class="price-current">Rp {{ number_format($finalPrice,0,',','.') }}</span>
                @endif
                <a href="{{ route('products.show', $product) }}" class="btn">Detail</a>
            </div>
        @endforeach
    </div>
    <div class="pagination">
        {{ $products->links() }}
    </div>
</section>
{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

{!! view()->file(base_path('themes/' . $theme . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $theme,
])->render() !!}
</body>
</html>
