@php
    $images = collect($product['images'] ?? []);
    $primaryImage = $product['primary_image'] ?? $images->first();
    $description = $product['description'] ?? null;
@endphp
<div id="productDetailSection" class="container py-5" data-product-id="{{ $product['id'] }}" data-cart-endpoint="{{ $product['cart_endpoint'] }}" data-csrf="{{ $product['csrf'] }}">
    <div class="row g-5 align-items-start">
        <div class="col-lg-6">
            <div class="position-relative overflow-hidden rounded">
                <img src="{{ $primaryImage }}" alt="{{ $product['name'] ?? 'Produk' }}" class="img-fluid w-100" id="mainProductImage">
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                @foreach($images as $index => $src)
                    <img src="{{ $src }}" data-full="{{ $src }}" class="img-thumbnail product-thumb {{ $index === 0 ? 'border-primary' : '' }}" style="width: 80px; height: 80px; object-fit: cover; cursor:pointer;" alt="{{ $product['name'] ?? 'Produk' }} thumbnail {{ $index + 1 }}">
                @endforeach
            </div>
        </div>
        <div class="col-lg-6">
            <h2 class="mb-3">{{ $product['name'] ?? 'Produk' }}</h2>
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                @if($product['has_promo'] ?? false)
                    <span class="promo-badge">{{ $product['promotion_label'] }}</span>
                @endif
                <div class="price-display">
                    @if(($product['has_promo'] ?? false) && !empty($product['price_original']))
                        <span class="price-original">Rp {{ number_format($product['price_original'], 0, ',', '.') }}</span>
                    @endif
                    <span class="price-current">Rp {{ number_format($product['price_current'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            <p class="mb-4">{{ $product['short_description'] ?? '' }}</p>
            <div class="d-flex align-items-center mb-3" id="quantityControl">
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" type="button" data-action="decrease">-</button>
                    <input type="number" class="form-control text-center" value="1" min="1" id="quantityInput">
                    <button class="btn btn-outline-secondary" type="button" data-action="increase">+</button>
                </div>
            </div>
            <div class="mb-4">
                <button class="btn btn-primary py-2 px-4 me-2" id="addToCartButton" type="button"><i class="bi bi-cart"></i> Masukkan ke Keranjang</button>
                <button class="btn btn-outline-primary py-2 px-4" type="button"><i class="bi bi-heart"></i></button>
            </div>
            <div class="cart-feedback" id="cartFeedback" role="status" aria-live="polite"></div>
            <div class="mt-4">
                <h5 class="mb-3">Deskripsi Produk</h5>
                <p>{!! $description ? nl2br(e($description)) : 'Belum ada deskripsi produk.' !!}</p>
            </div>
        </div>
    </div>
</div>
