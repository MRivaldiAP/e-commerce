<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-istudio';
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $settings = PageSetting::forPage('product-detail');
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
    $defaultHeroImage = $resolveMedia($settings['hero.image'] ?? null, $assetBase('img/hero-slider-1.jpg'));
    $primaryProductImage = $resolveMedia($product->image_url ?? null, $assetBase('img/project-1.jpg'));
    $heroBackground = $defaultHeroImage ?? $primaryProductImage;
    $heroTitle = $settings['hero.title'] ?? $product->name;
    $heroDescription = $settings['hero.description'] ?? ($product->short_description ?? null);

    $images = $product->images ?? collect();
    if (! $images instanceof \Illuminate\Support\Collection) {
        $images = collect($images);
    }

    $imageSources = collect();
    if ($primaryProductImage) {
        $imageSources->push($primaryProductImage);
    }
    foreach ($images as $image) {
        if (! empty($image->path)) {
            $url = $resolveMedia($image->path, null);
            if ($url && ! $imageSources->contains($url)) {
                $imageSources->push($url);
            }
        }
    }
    if ($imageSources->isEmpty()) {
        $imageSources->push($assetBase('img/project-1.jpg'));
    }

    $promotion = $product->currentPromotion();
    $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
    $finalPrice = $product->final_price;

    $quantityLabel = $settings['details.quantity_label'] ?? 'Jumlah';
    $addToCartLabel = $settings['details.add_to_cart_label'] ?? 'Masukkan ke Keranjang';
    $successFeedback = $settings['details.added_feedback'] ?? 'Produk ditambahkan ke keranjang.';
    $errorFeedback = $settings['details.error_feedback'] ?? 'Gagal menambahkan produk ke keranjang.';

    $commentsCollection = $comments ?? ($product->comments ?? collect());
    if (! $commentsCollection instanceof \Illuminate\Support\Collection) {
        $commentsCollection = collect($commentsCollection);
    }

    $commentsVisible = ($settings['comments.visible'] ?? '1') === '1';
    $commentsHeading = $settings['comments.heading'] ?? 'Ulasan Pelanggan';
    $commentsEmpty = $settings['comments.empty_text'] ?? 'Belum ada ulasan untuk produk ini.';

    $recommendationsVisible = ($settings['recommendations.visible'] ?? '1') === '1';
    $recommendationsHeading = $settings['recommendations.heading'] ?? 'Produk Terkait';
    $recommendationButtonLabel = $settings['recommendations.button_label'] ?? 'Detail';
    $recommendationsEmpty = $settings['recommendations.empty_text'] ?? 'Belum ada rekomendasi produk.';

    $recommendationsQuery = Product::query()
        ->where('id', '!=', $product->id)
        ->with(['images', 'promotions']);

    $categories = $product->relationLoaded('categories') ? $product->categories : $product->categories()->get();
    if ($categories && $categories->count()) {
        $recommendationsQuery->whereHas('categories', function ($query) use ($categories) {
            $query->whereIn('categories.id', $categories->pluck('id'));
        });
    }

    $recommendations = $recommendationsQuery->take(6)->get();
    if ($recommendations->count() < 4) {
        $fallback = Product::query()
            ->where('id', '!=', $product->id)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['images', 'promotions'])
            ->take(6 - $recommendations->count())
            ->get();
        $recommendations = $recommendations->merge($fallback);
    }
    $recommendations = $recommendations->take(6);

    $pageTitle = $heroTitle;
@endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($product->description ?? $heroDescription ?? ''), 160) }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    @if($imageSources->isNotEmpty())
        <meta property="og:image" content="{{ $imageSources->first() }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ $assetBase('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/style.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }

        #product-detail .detail-grid {
            display: grid;
            gap: 2.5rem;
        }

        @media (min-width: 992px) {
            #product-detail .detail-grid {
                grid-template-columns: 1.1fr 0.9fr;
                align-items: start;
            }
        }

        #product-detail .main-image {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 43, 0.18);
        }

        #product-detail .main-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        #product-detail .thumbnail-slider {
            margin-top: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        #product-detail .thumbnail-slider img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.75rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }

        #product-detail .thumbnail-slider img.active,
        #product-detail .thumbnail-slider img:hover {
            border-color: var(--bs-primary);
            transform: translateY(-2px);
        }

        #product-detail .product-info h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        #product-detail .price-block {
            margin: 1.5rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        #product-detail .price-original {
            font-size: 1rem;
            text-decoration: line-through;
            color: rgba(15, 23, 43, 0.5);
        }

        #product-detail .price-current {
            font-size: 2rem;
            font-weight: 700;
            color: var(--bs-primary);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        #product-detail .promo-pill {
            background: #dc3545;
            color: #fff;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        #product-detail .quantity-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(15, 23, 43, 0.2);
            border-radius: 999px;
            overflow: hidden;
        }

        #product-detail .quantity-control button {
            background: transparent;
            border: none;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            color: var(--bs-primary);
        }

        #product-detail .quantity-control input {
            width: 70px;
            border: none;
            text-align: center;
            font-weight: 600;
        }

        #product-detail .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        #product-detail .action-buttons .btn {
            border-radius: 999px;
            padding: 0.85rem 1.8rem;
            font-weight: 600;
        }

        #product-detail .cart-feedback {
            margin-top: 0.75rem;
            min-height: 1.25rem;
            color: var(--bs-primary);
        }

        #product-detail .cart-feedback.error {
            color: #dc3545;
        }

        #product-detail .description {
            margin-top: 2rem;
            line-height: 1.7;
            color: #5a5f69;
        }

        #product-detail .meta-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        #product-detail .meta-list span {
            background: rgba(15, 23, 43, 0.05);
            border-radius: 999px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }

        #recommendations .product-card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #recommendations .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 45px rgba(15, 23, 43, 0.12);
        }

        #recommendations .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        #recommendations .product-card .promo-label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dc3545;
            color: #fff;
            padding: 0.3rem 0.8rem;
            border-radius: 999px;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        #recommendations .product-card .price-original {
            display: block;
            color: rgba(15, 23, 43, 0.45);
            text-decoration: line-through;
            font-size: 0.85rem;
        }

        #recommendations .product-card .price-current {
            display: block;
            font-weight: 700;
            color: var(--bs-primary);
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
                    <h1 class="display-1 mb-0 animated slideInLeft">{{ $heroTitle }}</h1>
                </div>
                <div class="col-lg-6 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ route('products.index') }}">Produk</a></li>
                            <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $product->name }}</li>
                        </ol>
                    </nav>
                    @if($heroDescription)
                        <p class="text-muted mt-3 mb-0">{{ $heroDescription }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<section id="product-detail" class="container-fluid py-5">
    <div class="container py-5">
        <div class="detail-grid">
            <div>
                <div class="main-image">
                    <img src="{{ $imageSources->first() }}" alt="{{ $product->name }}" id="mainProductImage">
                </div>
                @if($imageSources->count() > 1)
                    <div class="thumbnail-slider" id="thumbnailSlider">
                        @foreach($imageSources as $index => $src)
                            <img src="{{ $src }}" data-full="{{ $src }}" class="thumbnail {{ $index === 0 ? 'active' : '' }}" alt="{{ $product->name }} thumbnail {{ $loop->iteration }}">
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="product-info">
                <h2>{{ $product->name }}</h2>
                <div class="price-block">
                    @if($hasPromo)
                        <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    @endif
                    <span class="price-current">Rp {{ number_format($finalPrice, 0, ',', '.') }}
                        @if($hasPromo && $promotion?->label)
                            <span class="promo-pill">{{ $promotion->label }}</span>
                        @endif
                    </span>
                </div>
                @if(!empty($product->short_description))
                    <p class="text-muted">{{ $product->short_description }}</p>
                @endif
                <div class="meta-list">
                    @if($product->sku)
                        <span>SKU: {{ $product->sku }}</span>
                    @endif
                    @if($categories && $categories->count())
                        @foreach($categories as $category)
                            <span>{{ $category->name }}</span>
                        @endforeach
                    @endif
                </div>
                <div class="mt-4">
                    <label for="quantityInput" class="form-label text-uppercase small fw-semibold text-muted">{{ $quantityLabel }}</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="quantity-control" id="quantityControl">
                            <button type="button" data-action="decrease" aria-label="Kurangi jumlah"><i class="bi bi-dash"></i></button>
                            <input type="number" id="quantityInput" value="1" min="1">
                            <button type="button" data-action="increase" aria-label="Tambah jumlah"><i class="bi bi-plus"></i></button>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-primary" id="addToCartButton" type="button">
                                <i class="bi bi-cart me-2"></i>{{ $addToCartLabel }}
                            </button>
                        </div>
                    </div>
                    <div class="cart-feedback" id="cartFeedback" role="status" aria-live="polite"></div>
                </div>
                <div class="description">
                    {!! $product->description ? nl2br(e($product->description)) : '<p>Belum ada deskripsi produk.</p>' !!}
                </div>
            </div>
        </div>
    </div>
</section>

@if($commentsVisible)
<section id="product-comments" class="container-fluid pb-5">
    <div class="container pb-5">
        <h3 class="text-center mb-4">{{ $commentsHeading }}</h3>
        @if($commentsCollection->isEmpty())
            <p class="text-center text-muted">{{ $commentsEmpty }}</p>
        @else
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @foreach($commentsCollection as $comment)
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-0">{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</h5>
                                    <small class="text-muted">{{ optional($comment->created_at)->format('d M Y') }}</small>
                                </div>
                                <p class="mb-0">{{ $comment->content }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endif

@if($recommendationsVisible)
<section id="recommendations" class="container-fluid py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="mb-3">{{ $recommendationsHeading }}</h2>
        </div>
        @if($recommendations->isEmpty())
            <div class="alert alert-light border text-center">{{ $recommendationsEmpty }}</div>
        @else
            <div class="row g-4">
                @foreach($recommendations as $item)
                    @php
                        $itemImagePath = $item->image_url ?? optional($item->images->first())->path;
                        $itemImageUrl = $resolveMedia($itemImagePath, $assetBase('img/project-2.jpg'));
                        $itemPromotion = $item->currentPromotion();
                        $itemHasPromo = $itemPromotion && $item->promo_price !== null && $item->promo_price < $item->price;
                        $itemFinalPrice = $item->final_price;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card product-card h-100 shadow-sm">
                            <div class="position-relative">
                                <img src="{{ $itemImageUrl }}" alt="{{ $item->name }}">
                                @if($itemHasPromo && $itemPromotion?->label)
                                    <span class="promo-label">{{ $itemPromotion->label }}</span>
                                @endif
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="card-text text-muted flex-grow-1">{{ Str::limit($item->short_description ?? $item->description, 120) }}</p>
                                <div class="d-flex justify-content-between align-items-end mt-3">
                                    <div>
                                        @if($itemHasPromo)
                                            <span class="price-original">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        @endif
                                        <span class="price-current">Rp {{ number_format($itemFinalPrice, 0, ',', '.') }}</span>
                                    </div>
                                    <a href="{{ route('products.show', $item->slug ?? $item->id) }}" class="btn btn-outline-primary">{{ $recommendationButtonLabel }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const thumbnails = document.querySelectorAll('#thumbnailSlider img');
        const mainImage = document.getElementById('mainProductImage');
        thumbnails.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const full = this.getAttribute('data-full');
                if (full) {
                    mainImage.src = full;
                }
            });
        });

        const control = document.getElementById('quantityControl');
        const input = document.getElementById('quantityInput');
        control?.addEventListener('click', function (event) {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }
            const action = button.getAttribute('data-action');
            let current = parseInt(input.value || '1', 10);
            if (action === 'increase') {
                current += 1;
            }
            if (action === 'decrease') {
                current = Math.max(1, current - 1);
            }
            input.value = current;
        });

        const addToCart = document.getElementById('addToCartButton');
        const feedback = document.getElementById('cartFeedback');
        const csrf = '{{ csrf_token() }}';
        const productId = {{ $product->id }};
        const endpoint = '{{ route('cart.items.store') }}';
        const successMessage = @json($successFeedback);
        const errorMessage = @json($errorFeedback);
        let feedbackTimer = null;

        function showFeedback(message, isError = false) {
            if (!feedback) {
                return;
            }

            if (feedbackTimer) {
                clearTimeout(feedbackTimer);
                feedbackTimer = null;
            }

            feedback.textContent = message || '';
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
                    return data?.message || errorMessage;
                }).catch(function () {
                    return errorMessage;
                });
            }

            return Promise.resolve(errorMessage);
        }

        addToCart?.addEventListener('click', function (event) {
            event.preventDefault();
            const quantity = Math.max(1, parseInt(input?.value || '1', 10));
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
                .then(function (data) {
                    showFeedback(successMessage, false);
                    window.dispatchEvent(new CustomEvent('cart:updated', { detail: data.summary }));
                })
                .catch(function (error) {
                    parseError(error).then(function (message) {
                        showFeedback(message, true);
                    });
                })
                .finally(function () {
                    addToCart.disabled = false;
                    addToCart.removeAttribute('aria-busy');
                });
        });
    });
</script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
