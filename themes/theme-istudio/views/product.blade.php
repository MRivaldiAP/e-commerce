<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@php
    use App\Models\Category;
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $themeName = $theme ?? 'theme-istudio';
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $settings = PageSetting::forPage('product');
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }

        return ThemeMedia::url($path) ?? $fallback;
    };

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroBackground = $resolveMedia($settings['hero.image'] ?? null, $assetBase('img/hero-slider-2.jpg'));
    $pageTitle = $settings['title'] ?? 'Produk';
    $heroDescription = $settings['hero.description'] ?? null;

    $searchPlaceholder = $settings['filters.search_placeholder'] ?? 'Cari produk...';
    $categoryLabel = $settings['filters.category_label'] ?? 'Kategori';
    $categoryAllLabel = $settings['filters.category_all_label'] ?? 'Semua Kategori';
    $sortLabel = $settings['filters.sort_label'] ?? 'Urutkan';
    $sortDefaultLabel = $settings['filters.sort_default_label'] ?? 'Urutkan';
    $sortPriceLowLabel = $settings['filters.sort_price_low_label'] ?? 'Harga Terendah';
    $sortPriceHighLabel = $settings['filters.sort_price_high_label'] ?? 'Harga Tertinggi';
    $sortPopularLabel = $settings['filters.sort_popular_label'] ?? 'Terpopuler';
    $applyLabel = $settings['filters.apply_label'] ?? 'Terapkan';
    $resetLabel = $settings['filters.reset_label'] ?? 'Atur Ulang';

    $query = Product::query()->with(['images', 'promotions']);
    if ($search = request('search')) {
        $query->where('name', 'like', "%{$search}%");
    }
    if ($category = request('category')) {
        $query->whereHas('categories', fn ($q) => $q->where('slug', $category));
    }
    if ($sort = request('sort')) {
        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'sold_desc') {
            $query->withCount('orderItems')->orderByDesc('order_items_count');
        }
    } else {
        $query->latest();
    }

    $products = $query->paginate(12)->withQueryString();
    $categories = Category::query()->orderBy('name')->get();

    $gridEmptyText = $settings['grid.empty_text'] ?? 'Belum ada produk tersedia.';
    $detailButtonLabel = $settings['grid.button_label'] ?? 'Detail';
@endphp
    <title>{{ $pageTitle }}</title>
    <link href="{{ $assetBase('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/style.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }

        #filters .form-control,
        #filters .form-select {
            min-height: 48px;
            border-radius: 30px;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        #filters .btn {
            border-radius: 30px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(15, 23, 43, 0.1);
        }

        .product-card .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .product-card .promo-label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            background: #dc3545;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .product-card .price-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.15rem;
        }

        .product-card .price-original {
            font-size: 0.85rem;
            text-decoration: line-through;
            color: rgba(15, 23, 43, 0.5);
        }

        .product-card .price-current {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--bs-primary);
        }

        .product-card .card-body {
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            gap: 0.75rem;
        }

        .product-card .card-body p {
            flex-grow: 1;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if($heroVisible)
    <div id="hero" class="container-fluid pb-5 bg-primary hero-header" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container py-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <h1 class="display-1 mb-0 text-white animated slideInLeft">{{ $pageTitle }}</h1>
                </div>
                <div class="col-lg-6 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $pageTitle }}</li>
                        </ol>
                    </nav>
                    @if($heroDescription)
                        <p class="text-white-50 mt-3 mb-0">{{ $heroDescription }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<section id="catalog" class="container-fluid py-5 bg-light">
    <div class="container py-5">
        <div id="filters" class="card border-0 shadow-sm mb-5">
            <div class="card-body p-4">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-12 col-lg-4">
                        <label for="search" class="form-label text-uppercase small fw-semibold text-muted">{{ $settings['filters.search_label'] ?? 'Pencarian' }}</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="{{ $searchPlaceholder }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="category" class="form-label text-uppercase small fw-semibold text-muted">{{ $categoryLabel }}</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">{{ $categoryAllLabel }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="sort" class="form-label text-uppercase small fw-semibold text-muted">{{ $sortLabel }}</label>
                        <select id="sort" name="sort" class="form-select">
                            <option value="">{{ $sortDefaultLabel }}</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ $sortPriceLowLabel }}</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ $sortPriceHighLabel }}</option>
                            <option value="sold_desc" @selected(request('sort') === 'sold_desc')>{{ $sortPopularLabel }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end align-items-end">
                        <button type="submit" class="btn btn-primary w-100 w-lg-auto">{{ $applyLabel }}</button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 w-lg-auto">{{ $resetLabel }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            @forelse($products as $product)
                @php
                    $imagePath = $product->image_url ?? optional($product->images->first())->path;
                    $imageUrl = $resolveMedia($imagePath, $assetBase('img/project-1.jpg'));
                    $promotion = $product->currentPromotion();
                    $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
                    $finalPrice = $product->final_price;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 product-card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="product-image">
                            @if($hasPromo && $promotion?->label)
                                <span class="promo-label">{{ $promotion->label }}</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <h3 class="h5 mb-1">{{ $product->name }}</h3>
                            <p class="mb-0">{{ \Illuminate\Support\Str::limit($product->short_description ?? $product->description, 110) }}</p>
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <div class="price-stack text-end">
                                    @if($hasPromo)
                                        <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="price-current">Rp {{ number_format($finalPrice, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="btn btn-outline-primary px-4">{{ $detailButtonLabel }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border mb-0">{{ $gridEmptyText }}</div>
                </div>
            @endforelse
        </div>

        <div class="mt-5">
            {{ $products->links() }}
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ $assetBase('lib/wow/wow.min.js') }}"></script>
<script src="{{ $assetBase('lib/easing/easing.min.js') }}"></script>
<script src="{{ $assetBase('lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ $assetBase('lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ $assetBase('js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
