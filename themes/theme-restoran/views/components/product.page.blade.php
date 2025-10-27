@php
    use App\Models\Category;
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-restoran';
    $pageSettings = PageSetting::forPage('product');
    $settings = array_merge($pageSettings, $settings ?? []);

    $query = Product::query()->with(['images', 'promotions']);
    $search = request('search');
    $category = request('category');
    $sort = request('sort');

    if ($search) {
        $query->where('name', 'like', "%{$search}%");
    }

    if ($category) {
        $query->whereHas('categories', fn ($q) => $q->where('slug', $category));
    }

    if ($sort) {
        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'sold_desc') {
            $query->withCount('orderItems')->orderByDesc('order_items_count');
        }
    }

    $products = $query->paginate(15)->withQueryString();
    $categories = Category::all();
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $activeSections = PageElements::activeSectionKeys($themeName, $settings);
    $heroActive = in_array('hero', $activeSections, true);

    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null);
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');

    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }

    if ($heroBackground) {
        $heroStyle = $heroMaskEnabled
            ? "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroBackground}'); background-size: cover; background-position: center;"
            : "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
    } else {
        $heroStyle = $heroMaskEnabled
            ? 'background: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9));'
            : 'background: var(--theme-accent);';
    }

    $productSection = [
        'filters' => [
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
            'categories' => $categories,
        ],
        'products' => $products,
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $settings['title'] ?? 'Produk' }}</title>
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
    @include('themeRestoran::components.palette', ['theme' => $themeName])
    <style>
        .promo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--theme-accent, #fea116);
            color: #fff;
            font-size: 0.7rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        .price-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.15rem;
        }

        .price-stack .price-original {
            font-size: 0.85rem;
            text-decoration: line-through;
            color: rgba(0, 0, 0, 0.45);
        }

        .price-stack .price-current {
            font-weight: 700;
            color: var(--theme-accent, #fea116);
        }
    </style>
</head>
<body>
<div class="container-xxl position-relative p-0">
    @include('themeRestoran::components.nav-menu', [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])

    @if($heroActive && ($settings['hero.visible'] ?? '1') === '1')
        @include('themeRestoran::components.product.sections.hero', [
            'hero' => [
                'visible' => true,
                'classes' => $heroClasses,
                'style' => $heroStyle,
                'title' => $settings['title'] ?? 'Produk Kami',
            ],
        ])
    @endif
</div>

@include('themeRestoran::components.product.sections.list', ['list' => $productSection])

@include('themeRestoran::components.footer', ['footer' => $footerConfig])

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

@include('themeRestoran::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
