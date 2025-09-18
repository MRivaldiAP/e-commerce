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
        #product-detail .price { font-size: 1.5rem; font-weight: 600; margin: 1rem 0; }
        #product-detail .quantity-control { display: inline-flex; align-items: center; border: 1px solid var(--color-secondary); border-radius: 30px; overflow: hidden; }
        #product-detail .quantity-control button { background: transparent; border: none; padding: 0.5rem 1rem; font-size: 1.1rem; cursor: pointer; }
        #product-detail .quantity-control input { width: 60px; text-align: center; border: none; font-size: 1rem; }
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
    use App\Models\Product;
    use App\Support\Cart;

    $settings = PageSetting::where('theme', $theme)->where('page', 'product-detail')->pluck('value', 'key')->toArray();

    $navLinks = [
        ['label' => 'Homepage', 'href' => url('/'), 'visible' => true],
        ['label' => 'Produk', 'href' => url('/produk'), 'visible' => true],
        ['label' => 'Keranjang', 'href' => url('/keranjang'), 'visible' => true],
    ];
    $cartSummary = Cart::summary();

    $footerLinks = [
        ['label' => 'Privacy Policy', 'href' => '#', 'visible' => ($settings['footer.privacy'] ?? '0') == '1'],
        ['label' => 'Terms & Conditions', 'href' => '#', 'visible' => ($settings['footer.terms'] ?? '0') == '1'],
    ];

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
    $recommendations = $recommendationsQuery->with('images')->take(5)->get();
    if ($recommendations->count() < 5) {
        $fallback = Product::where('id', '!=', $product->id)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with('images')
            ->take(5 - $recommendations->count())
            ->get();
        $recommendations = $recommendations->concat($fallback);
    }
@endphp
{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), ['links' => $navLinks, 'cart' => $cartSummary])->render() !!}

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
            <p class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            <div class="quantity-control" id="quantityControl">
                <button type="button" data-action="decrease">-</button>
                <input type="number" value="1" min="1" id="quantityInput">
                <button type="button" data-action="increase">+</button>
            </div>
            <button class="cta" id="addToCartButton">Masukkan ke Keranjang</button>
            <p class="cart-feedback" id="cartFeedback" role="status"></p>
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
            @php $img = optional($item->images->first())->path; @endphp
            <div class="product-card">
                <img src="{{ $img ? asset('storage/'.$img) : 'https://via.placeholder.com/150' }}" alt="{{ $item->name }}">
                <h3>{{ $item->name }}</h3>
                <p>{{ $item->price_formatted ?? number_format($item->price,0,',','.') }}</p>
                <a href="{{ route('products.show', $item) }}" class="btn">Detail</a>
            </div>
        @endforeach
    </div>
</section>
@endif

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'links' => $footerLinks,
    'copyright' => $settings['footer.copyright'] ?? ('© '.date('Y') . ' Herbal Green')
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
</body>
</html>
