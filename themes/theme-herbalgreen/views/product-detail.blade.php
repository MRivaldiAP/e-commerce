<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
    <style>
        #product-detail .detail-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); align-items: start; }
        #product-detail .main-image { width: 100%; border-radius: 8px; overflow: hidden; }
        #product-detail .main-image img { width: 100%; height: auto; display: block; }
        #product-detail .thumbnail-slider { margin-top: 1rem; display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; }
        #product-detail .thumbnail-slider img { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: border 0.2s ease; }
        #product-detail .thumbnail-slider img.active { border-color: var(--color-primary); }
        #product-detail .product-info { text-align: left; }
        #product-detail .price { font-size: 1.5rem; font-weight: 600; margin: 1rem 0; display: flex; flex-direction: column; gap: .35rem; }
        #product-detail .price .price-original { color: #9e9e9e; text-decoration: line-through; font-size: 1rem; }
        #product-detail .price .price-current { color: #2e7d32; font-weight: 700; font-size: 1.75rem; display: inline-flex; align-items: center; gap: .75rem; }
        #product-detail .promo-pill { background: #e53935; color: #fff; padding: 4px 12px; border-radius: 999px; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; }
        .product-card .promo-label { position: absolute; top: 12px; left: 12px; background:#e53935; color:#fff; padding:4px 12px; border-radius:999px; font-size:.72rem; text-transform:uppercase; font-weight:600; letter-spacing:.04em; }
        #recommendations .product-card { position: relative; }
        #recommendations .product-card .price-original { display:block; color:#9e9e9e; text-decoration:line-through; }
        #recommendations .product-card .price-current { display:block; color:#2e7d32; font-weight:700; }
        #product-detail .quantity-control { display: inline-flex; align-items: center; border: 1px solid var(--color-secondary); border-radius: 30px; overflow: hidden; }
        #product-detail .quantity-control button { background: transparent; border: none; padding: 0.5rem 1rem; font-size: 1.1rem; cursor: pointer; }
        #product-detail .quantity-control input { width: 60px; text-align: center; border: none; font-size: 1rem; }
        #product-detail .quantity-label { display: block; font-weight: 600; margin-bottom: 0.75rem; }
        #product-detail .description { margin-top: 1.5rem; line-height: 1.6; }
        #comments { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        #comments .comment { padding: 1.5rem; border-bottom: 1px solid #e0f2f1; }
        #comments .comment:last-child { border-bottom: none; }
        #comments .comment strong { display: block; margin-bottom: 0.5rem; }
        #recommendations .product-card .btn { margin-top: 1rem; display: inline-block; }
        #product-detail .cart-feedback { margin-top: 0.75rem; color: var(--color-primary); min-height: 1.25rem; }
        #product-detail .cart-feedback.error { color: #d32f2f; }
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Setting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $settings = PageSetting::forPage('product-detail');

    $navigation = LayoutSettings::navigation($theme);
    $footerConfig = LayoutSettings::footer($theme);
    $cartSummary = Cart::summary();

    $paymentActive = ! empty(Setting::getValue('payment.gateway'));
    $quantityLabel = $settings['details.quantity_label'] ?? 'Jumlah';
    $addToCartLabel = $settings['details.add_to_cart_label'] ?? 'Masukkan ke Keranjang';
    $whatsappNumberRaw = trim((string) ($settings['details.whatsapp_number'] ?? ''));
    $whatsappDigits = $whatsappNumberRaw !== '' ? preg_replace('/\D+/', '', $whatsappNumberRaw) : '';
    $whatsappMessage = 'Halo, saya ingin memesan ' . $product->name . '.';
    $whatsappUrl = $whatsappDigits !== '' ? 'https://wa.me/' . $whatsappDigits . '?text=' . rawurlencode($whatsappMessage) : null;

    $images = $product->images ?? collect();
    $primaryImage = optional($images->first())->path;
    $imageSources = $images->pluck('path')->filter()->map(fn($path) => asset('storage/'.$path))->values();
    if ($imageSources->isEmpty()) {
        $imageSources = collect(['https://via.placeholder.com/600x400?text=No+Image']);
        $primaryImage = null;
    }

    $comments = $product->comments ?? collect();

    $recommendationsQuery = Product::query()->where('id', '!=', $product->id);
    if ($product->categories && $product->categories->count()) {
        $recommendationsQuery->whereHas('categories', fn($q) => $q->whereIn('categories.id', $product->categories->pluck('id')));
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
        <h1>{{ $settings['hero.title'] ?? $product->name }}</h1>
        <p>{{ $product->short_description ?? 'Temukan detail lengkap produk pilihan Anda.' }}</p>
    </div>
</section>
@endif

<section id="product-detail" class="products">
    <h2>{{ $product->name }}</h2>
    <div class="product-grid detail-grid">
        <div class="product-card">
            <div class="main-image">
                @php $mainImageUrl = $primaryImage ? asset('storage/'.$primaryImage) : $imageSources->first(); @endphp
                <img src="{{ $mainImageUrl }}" alt="{{ $product->name }}" id="mainProductImage">
            </div>
            <div class="thumbnail-slider">
                @foreach($imageSources as $index => $src)
                    <img src="{{ $src }}" data-full="{{ $src }}" class="thumbnail {{ $index === 0 ? 'active' : '' }}" alt="{{ $product->name }} thumbnail {{ $index + 1 }}">
                @endforeach
            </div>
        </div>
        <div class="product-card product-info">
            <h3>{{ $product->name }}</h3>
            <div class="price">
                @if($productHasPromo)
                    <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    <span class="price-current">Rp {{ number_format($productFinalPrice, 0, ',', '.') }}<span class="promo-pill">{{ $productPromotion->label }}</span></span>
                @else
                    <span class="price-current">Rp {{ number_format($productFinalPrice, 0, ',', '.') }}</span>
                @endif
            </div>
            @if($paymentActive)
                <label for="quantityInput" class="quantity-label" id="quantityLabel">{{ $quantityLabel }}</label>
                <div class="quantity-control" id="quantityControl" aria-labelledby="quantityLabel">
                    <button type="button" data-action="decrease">-</button>
                    <input type="number" value="1" min="1" id="quantityInput" aria-label="{{ $quantityLabel }}">
                    <button type="button" data-action="increase">+</button>
                </div>
                <button class="cta" id="addToCartButton">{{ $addToCartLabel }}</button>
                <p class="cart-feedback" id="cartFeedback" role="status" aria-live="polite"></p>
            @else
                <a class="cta{{ $whatsappUrl ? '' : ' disabled' }}" id="orderViaWhatsappButton" href="{{ $whatsappUrl ?? '#' }}" @if($whatsappUrl) target="_blank" rel="noopener noreferrer" @else aria-disabled="true" @endif>
                    Pesan Sekarang
                </a>
                @if(! $whatsappUrl)
                    <p class="cart-feedback error" role="status">Silakan isi nomor WhatsApp di halaman kelola detail produk.</p>
                @endif
            @endif
            <div class="description">
                {!! $product->description ? nl2br(e($product->description)) : '<p>Belum ada deskripsi produk.</p>' !!}
            </div>
        </div>
    </div>
</section>

@if(($settings['comments.visible'] ?? '1') == '1')
<section id="comments" class="section">
    <h2>{{ $settings['comments.heading'] ?? 'Komentar Produk' }}</h2>
    @if($comments->isEmpty())
        <p class="text-center">Belum ada komentar.</p>
    @else
        @foreach($comments as $comment)
            <div class="comment">
                <strong>{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</strong>
                <small>{{ optional($comment->created_at)->format('d M Y') }}</small>
                <p>{{ $comment->content }}</p>
            </div>
        @endforeach
    @endif
</section>
@endif

@if(($settings['recommendations.visible'] ?? '1') == '1' && $recommendations->count())
<section id="recommendations" class="products">
    <h2>{{ $settings['recommendations.heading'] ?? 'Produk Serupa' }}</h2>
    <div class="product-grid">
        @foreach($recommendations as $item)
            @php
                $img = optional($item->images->first())->path;
                $itemPromotion = $item->currentPromotion();
                $itemHasPromo = $itemPromotion && $item->promo_price !== null && $item->promo_price < $item->price;
                $itemFinalPrice = $item->final_price;
            @endphp
            <div class="product-card">
                <img src="{{ $img ? asset('storage/'.$img) : 'https://via.placeholder.com/150' }}" alt="{{ $item->name }}">
                @if($itemHasPromo)
                    <span class="promo-label">{{ $itemPromotion->label }}</span>
                @endif
                <h3>{{ $item->name }}</h3>
                @if($itemHasPromo)
                    <span class="price-original">Rp {{ number_format($item->price,0,',','.') }}</span>
                    <span class="price-current">Rp {{ number_format($itemFinalPrice,0,',','.') }}</span>
                @else
                    <span class="price-current">Rp {{ number_format($itemFinalPrice,0,',','.') }}</span>
                @endif
                <a href="{{ route('products.show', $item) }}" class="btn">Detail</a>
            </div>
        @endforeach
    </div>
</section>
@endif

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script>
    document.addEventListener('DOMContentLoaded', function(){
        const thumbnails = document.querySelectorAll('#product-detail .thumbnail-slider img');
        const mainImage = document.getElementById('mainProductImage');
        thumbnails.forEach(function(thumb){
            thumb.addEventListener('click', function(){
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const full = this.getAttribute('data-full');
                if(full){
                    mainImage.src = full;
                }
            });
        });

        const control = document.getElementById('quantityControl');
        const input = document.getElementById('quantityInput');
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

        const addToCart = document.getElementById('addToCartButton');
        const feedback = document.getElementById('cartFeedback');
        const csrf = '{{ csrf_token() }}';
        const productId = {{ $product->id }};

        function showFeedback(message, isError = false) {
            if (!feedback) return;
            feedback.textContent = message;
            feedback.classList.toggle('error', !!isError);
            if (message) {
                feedback.classList.add('visible');
                setTimeout(() => feedback.classList.remove('visible'), 2600);
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
                    return data.message || 'Gagal menambahkan produk ke keranjang.';
                }).catch(function () {
                    return 'Gagal menambahkan produk ke keranjang.';
                });
            }
            return Promise.resolve('Gagal menambahkan produk ke keranjang.');
        }

        if(addToCart){
            addToCart.addEventListener('click', function(event){
                event.preventDefault();
                const inputField = document.getElementById('quantityInput');
                const quantity = Math.max(1, parseInt(inputField?.value || '1', 10));

                fetch('{{ route('cart.items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
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
                });
            });
        }
    });
</script>

{!! view()->file(base_path('themes/' . $theme . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $theme,
])->render() !!}
</body>
</html>
