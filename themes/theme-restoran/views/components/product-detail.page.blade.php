@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Support\PageElements;

    $themeName = $theme ?? 'theme-restoran';
    $pageSettings = PageSetting::forPage('product-detail', $themeName);
    $settings = array_merge($pageSettings, $settings ?? []);
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $promotion = $product->currentPromotion();
    $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
    $finalPrice = $product->final_price;

    $activeSections = PageElements::activeSectionKeys('product-detail', $themeName, $settings);
    $heroActive = in_array('hero', $activeSections, true);
    $commentsActive = in_array('comments', $activeSections, true);
    $recommendationsActive = in_array('recommendations', $activeSections, true);

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

    $images = ($product->images ?? collect())->pluck('path')->filter()->map(fn ($path) => asset('storage/' . ltrim($path, '/')))->values();

    if ($images->isEmpty()) {
        $images = collect(['https://via.placeholder.com/600x400?text=No+Image']);
    }

    $heroSection = [
        'visible' => $heroActive && ($settings['hero.visible'] ?? '1') === '1',
        'classes' => $heroClasses,
        'style' => $heroStyle,
        'title' => $settings['hero.title'] ?? $product->name,
        'breadcrumbTitle' => $product->name,
    ];

    $productSection = [
        'id' => $product->id,
        'name' => $product->name,
        'primary_image' => $images->first(),
        'images' => $images,
        'has_promo' => $hasPromo,
        'promotion_label' => $promotion?->label,
        'price_original' => $product->price,
        'price_current' => $finalPrice,
        'short_description' => $product->short_description ?? 'Nikmati cita rasa terbaik dari produk pilihan kami.',
        'description' => $product->description,
        'cart_endpoint' => route('cart.items.store'),
        'csrf' => csrf_token(),
    ];

    $commentsSection = [
        'visible' => $commentsActive && ($settings['comments.visible'] ?? '1') === '1',
        'heading' => $settings['comments.heading'] ?? 'Komentar Pelanggan',
        'items' => ($product->comments ?? collect())->map(function ($comment) {
            return [
                'author' => $comment->user?->name ?? $comment->name ?? 'Pengguna',
                'date' => optional($comment->created_at)->format('d M Y'),
                'content' => $comment->content,
            ];
        })->values()->all(),
    ];

    $recommendationsQuery = Product::query()->where('id', '!=', $product->id);

    if ($product->categories && $product->categories->count()) {
        $recommendationsQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')));
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

    $recommendationsSection = [
        'visible' => $recommendationsActive && ($settings['recommendations.visible'] ?? '1') === '1' && $recommendations->count(),
        'heading' => $settings['recommendations.heading'] ?? 'Produk Serupa',
        'items' => $recommendations->map(function ($item) {
            $imagePath = optional($item->images->first())->path;
            $itemPromotion = $item->currentPromotion();
            $itemHasPromo = $itemPromotion && $item->promo_price !== null && $item->promo_price < $item->price;

            return [
                'name' => $item->name,
                'image' => $imagePath ? asset('storage/' . ltrim($imagePath, '/')) : asset('storage/themes/theme-restoran/img/menu-1.jpg'),
                'has_promo' => $itemHasPromo,
                'promotion_label' => $itemPromotion?->label,
                'price_original' => $item->price,
                'price_current' => $item->final_price,
                'description' => \Illuminate\Support\Str::limit($item->short_description ?? $item->description, 80),
                'url' => route('products.show', $item),
            ];
        })->values()->all(),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $settings['hero.title'] ?? 'Detail Produk' }}</title>
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
        .cart-feedback {
            margin-top: 0.5rem;
            color: var(--bs-primary);
            min-height: 1.25rem;
            font-weight: 600;
        }

        .cart-feedback.error {
            color: #dc3545;
        }

        .promo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--theme-accent, #fea116);
            color: #fff;
            font-size: 0.75rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        .price-display {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .price-display .price-original {
            text-decoration: line-through;
            color: rgba(0, 0, 0, 0.45);
            font-size: 1rem;
        }

        .price-display .price-current {
            color: var(--theme-accent, #fea116);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .recommendation-card .price-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.2rem;
        }

        .recommendation-card .price-original {
            text-decoration: line-through;
            color: rgba(0, 0, 0, 0.45);
            font-size: 0.85rem;
        }

        .recommendation-card .price-current {
            color: var(--theme-accent, #fea116);
            font-weight: 600;
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

    @if($heroSection['visible'] ?? false)
        @include('themeRestoran::components.product-detail.sections.hero', ['hero' => $heroSection])
    @endif
</div>

@include('themeRestoran::components.product-detail.sections.details', ['product' => $productSection])

@if($commentsSection['visible'] ?? false)
    @include('themeRestoran::components.product-detail.sections.comments', ['comments' => $commentsSection])
@endif

@if($recommendationsSection['visible'] ?? false)
    @include('themeRestoran::components.product-detail.sections.recommendations', ['recommendations' => $recommendationsSection])
@endif

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
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const detailSection = document.getElementById('productDetailSection');
        if(!detailSection){
            return;
        }
        const thumbs = detailSection.querySelectorAll('.product-thumb');
        const mainImage = detailSection.querySelector('#mainProductImage');
        thumbs.forEach(function(thumb){
            thumb.addEventListener('click', function(){
                thumbs.forEach(t => t.classList.remove('border-primary'));
                this.classList.add('border-primary');
                const full = this.getAttribute('data-full');
                if(full && mainImage){
                    mainImage.src = full;
                }
            });
        });

        const control = detailSection.querySelector('#quantityControl');
        const input = detailSection.querySelector('#quantityInput');
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

        const addToCart = detailSection.querySelector('#addToCartButton');
        const feedback = detailSection.querySelector('#cartFeedback');
        const quantityInput = detailSection.querySelector('#quantityInput');
        const csrf = detailSection.getAttribute('data-csrf');
        const productId = detailSection.getAttribute('data-product-id');
        const endpoint = detailSection.getAttribute('data-cart-endpoint');
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
                feedbackTimer = setTimeout(function(){
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
                return error.json().then(function(data){
                    return data?.message || 'Gagal menambahkan produk ke keranjang.';
                }).catch(function(){
                    return 'Gagal menambahkan produk ke keranjang.';
                });
            }

            return Promise.resolve('Gagal menambahkan produk ke keranjang.');
        }

        if(addToCart){
            addToCart.addEventListener('click', function(event){
                event.preventDefault();

                const quantity = Math.max(1, parseInt(quantityInput?.value || '1', 10));
                addToCart.disabled = true;
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
                .then(function(data){
                    showFeedback('Produk ditambahkan ke keranjang.');
                    window.dispatchEvent(new CustomEvent('cart:updated', { detail: data.summary }));
                })
                .catch(function(error){
                    parseError(error).then(function(message){
                        showFeedback(message, true);
                    });
                })
                .finally(function(){
                    addToCart.disabled = false;
                    addToCart.removeAttribute('aria-busy');
                });
            });
        }
    });
</script>

@include('themeRestoran::components.floating-contact-buttons', ['theme' => $themeName])
</body>
</html>
