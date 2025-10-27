@php
    use App\Models\Category;
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-second';
    $settings = PageSetting::forPage('product');
    $activeSections = PageElements::activeSectionKeys('product', $themeName, $settings);
    $query = Product::query()->with(['images', 'promotions']);

    $filters = [
        'search' => request('search'),
        'category' => request('category'),
        'minprice' => request('minprice'),
        'maxprice' => request('maxprice'),
        'sort' => request('sort'),
    ];

    if ($filters['search']) {
        $query->where('name', 'like', '%' . $filters['search'] . '%');
    }
    if ($filters['category']) {
        $query->whereHas('categories', fn ($builder) => $builder->where('slug', $filters['category']));
    }
    if ($filters['minprice']) {
        $query->where('price', '>=', $filters['minprice']);
    }
    if ($filters['maxprice']) {
        $query->where('price', '<=', $filters['maxprice']);
    }
    if ($filters['sort']) {
        if ($filters['sort'] === 'price_asc') {
            $query->orderBy('price');
        } elseif ($filters['sort'] === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($filters['sort'] === 'sold_desc') {
            $query->withCount('orderItems')->orderByDesc('order_items_count');
        }
    }

    $products = $query->paginate(15)->withQueryString();
    $categories = Category::all();
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/breadcrumb.jpg');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['title'] ?? 'Produk' }}</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/style.css') }}" type="text/css">
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
@include('themeSecond::components.nav-menu', [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])

@foreach ($activeSections as $sectionKey)
    @if($sectionKey === 'hero')
        @include('themeSecond::components.product.sections.hero', [
            'settings' => $settings,
            'heroBackground' => $heroBackground,
        ])
    @endif
@endforeach

@include('themeSecond::components.product.sections.list', [
    'settings' => $settings,
    'products' => $products,
    'categories' => $categories,
    'filters' => $filters,
    'theme' => $themeName,
])

@include('themeSecond::components.footer', ['footer' => $footerConfig])

<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/' . $themeName . '/js/main.js') }}"></script>
<script>
    $(function () {
        var min = {{ $filters['minprice'] ?? 0 }};
        var max = {{ $filters['maxprice'] ?? 1000000 }};
        $("#price-range").slider({
            range: true,
            min: 0,
            max: 1000000,
            values: [min, max],
            slide: function (event, ui) {
                $("#minamount").val(ui.values[0]);
                $("#maxamount").val(ui.values[1]);
            }
        });
        $("#minamount").val($("#price-range").slider("values", 0));
        $("#maxamount").val($("#price-range").slider("values", 1));
    });
    document.querySelectorAll('[data-setbg]').forEach(function (el) {
        const bg = el.getAttribute('data-setbg');
        if (bg) {
            el.style.backgroundImage = `url(${bg})`;
        }
    });
</script>

@include('themeSecond::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
