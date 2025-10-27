<section id="product-detail" class="products">
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
        #product-detail .quantity-control { display: inline-flex; align-items: center; border: 1px solid var(--color-secondary); border-radius: 30px; overflow: hidden; }
        #product-detail .quantity-control button { background: transparent; border: none; padding: 0.5rem 1rem; font-size: 1.1rem; cursor: pointer; }
        #product-detail .quantity-control input { width: 60px; text-align: center; border: none; font-size: 1rem; }
        #product-detail .description { margin-top: 1.5rem; line-height: 1.6; }
        #product-detail .cart-feedback { margin-top: 0.75rem; color: var(--color-primary); min-height: 1.25rem; }
        #product-detail .cart-feedback.error { color: #d32f2f; }
    </style>
    <h2>{{ $product->name }}</h2>
    <div class="product-grid detail-grid">
        <div class="product-card">
            <div class="main-image">
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
</section>
