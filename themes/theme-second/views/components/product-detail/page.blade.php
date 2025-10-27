@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $themeName = $theme ?? 'theme-second';
    /** @var \App\Models\Product $product */
    $settings = PageSetting::forPage('product-detail');
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $images = $product->images ?? collect();
    $imageSources = $images->pluck('path')->filter()->map(fn ($path) => asset('storage/' . $path))->values();
    if ($imageSources->isEmpty()) {
        $imageSources = collect(['https://via.placeholder.com/600x400?text=No+Image']);
    }

    $comments = $product->comments ?? collect();

    $recommendationsQuery = Product::query()->where('id', '!=', $product->id);
    if ($product->categories && $product->categories->count()) {
        $recommendationsQuery->whereHas('categories', fn ($builder) => $builder->whereIn('categories.id', $product->categories->pluck('id')));
    }
    $recommendations = $recommendationsQuery->with(['images', 'promotions'])->take(5)->get();
    if ($recommendations->count() < 5) {
        $fallback = Product::where('id', '!=', $product->id)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['images', 'promotions'])
            ->take(5 - $recommendations->count())
            ->get();
        $recommendations = $recommendations->concat($fallback);
    }

    $productPromotion = $product->currentPromotion();
    $productHasPromo = $productPromotion && $product->promo_price !== null && $product->promo_price < $product->price;
    $productFinalPrice = $product->final_price;

    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? null)
        ?? asset('storage/themes/' . $themeName . '/img/breadcrumb.jpg');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['hero.title'] ?? $product->name }}</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/' . $themeName . '/css/style.css') }}" type="text/css">
    <style>
        .cart-feedback { margin-top: 1rem; color: #7fad39; min-height: 1.25rem; font-weight: 500; }
        .cart-feedback.error { color: #d9534f; }
        .product__details__price { display: flex; flex-direction: column; gap: .35rem; }
        .product__details__price .price-original { color: #9e9e9e; text-decoration: line-through; font-size: 1rem; }
        .product__details__price .price-current { color: #e65100; font-weight: 700; font-size: 1.75rem; display: flex; align-items: center; gap: .75rem; }
        .product__details__price .promo-pill { background: #e53935; color: #fff; padding: 4px 12px; border-radius: 999px; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; }
        .product__item__badge { position: absolute; top: 12px; left: 12px; background: #e53935; color: #fff; padding: 4px 12px; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .product__item__text .price-original { display: block; font-size: .85rem; color: #9e9e9e; text-decoration: line-through; }
        .product__item__text .price-discount { display: block; font-weight: 700; color: #e65100; }
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

@include('themeSecond::components.product-detail.sections.hero', [
    'settings' => $settings,
    'product' => $product,
    'heroBackground' => $heroBackground,
])

@include('themeSecond::components.product-detail.sections.details', [
    'settings' => $settings,
    'product' => $product,
    'images' => $imageSources,
    'productHasPromo' => $productHasPromo,
    'productPromotion' => $productPromotion,
    'productFinalPrice' => $productFinalPrice,
    'comments' => $comments,
])

@includeWhen(($settings['recommendations.visible'] ?? '1') === '1' && $recommendations->count(), 'themeSecond::components.product-detail.sections.recommendations', [
    'settings' => $settings,
    'recommendations' => $recommendations,
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
    document.addEventListener('DOMContentLoaded', function () {
        const addToCart = document.getElementById('addToCartButton');
        const feedback = document.getElementById('cartFeedback');
        const quantityInput = document.getElementById('quantityInput');
        const csrf = '{{ csrf_token() }}';
        const productId = {{ $product->id }};
        const endpoint = '{{ route('cart.items.store') }}';
        let feedbackTimer = null;

        function showFeedback(message, isError = false) {
            if (!feedback) {
                return;
            }
            if (feedbackTimer) {
                clearTimeout(feedbackTimer);
                feedbackTimer = null;
            }
            feedback.textContent = message;
            feedback.classList.toggle('error', Boolean(isError));
            if (message) {
                feedbackTimer = setTimeout(function () {
                    feedback.textContent = '';
                    feedback.classList.remove('error');
                }, 2600);
            }
        }

        function handleResponse(response) {
            if (!response.ok) {
                throw response;
            }
            return response.json();
        }

        function parseError(error) {
            if (typeof error.json === 'function') {
                return error.json().then(function (data) {
                    return data?.message || 'Gagal menambahkan produk ke keranjang.';
                }).catch(function () {
                    return 'Gagal menambahkan produk ke keranjang.';
                });
            }
            return Promise.resolve('Gagal menambahkan produk ke keranjang.');
        }

        if (addToCart) {
            addToCart.addEventListener('click', function (event) {
                event.preventDefault();
                const quantity = Math.max(1, parseInt(quantityInput?.value || '1', 10));
                addToCart.classList.add('disabled');
                addToCart.setAttribute('aria-busy', 'true');

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                    })
                })
                    .then(handleResponse)
                    .then(function (data) {
                        showFeedback('Produk ditambahkan ke keranjang.');
                        window.dispatchEvent(new CustomEvent('cart:updated', { detail: data.summary }));
                    })
                    .catch(function (error) {
                        parseError(error).then(function (message) {
                            showFeedback(message, true);
                        });
                    })
                    .finally(function () {
                        addToCart.classList.remove('disabled');
                        addToCart.removeAttribute('aria-busy');
                    });
            });
        }
    });

    document.querySelectorAll('[data-imgbigurl]').forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            const largeImage = document.getElementById('mainProductImage');
            const target = this.getAttribute('data-imgbigurl');
            if (largeImage && target) {
                largeImage.src = target;
            }
        });
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
