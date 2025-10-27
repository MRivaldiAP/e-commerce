<section id="recommendations" class="products">
    <style>
        #recommendations .product-card { position: relative; }
        #recommendations .product-card .price-original { display:block; color:#9e9e9e; text-decoration:line-through; }
        #recommendations .product-card .price-current { display:block; color:#2e7d32; font-weight:700; }
        #recommendations .product-card .btn { margin-top: 1rem; display: inline-block; }
        #recommendations .product-card .promo-label { position: absolute; top: 12px; left: 12px; background:#e53935; color:#fff; padding:4px 12px; border-radius:999px; font-size:.72rem; text-transform:uppercase; font-weight:600; letter-spacing:.04em; }
    </style>
    <h2>{{ $settings['recommendations.heading'] ?? 'Produk Serupa' }}</h2>
    <div class="product-grid">
        @foreach($recommendations as $item)
            @php
                $img = optional($item->images->first())->path;
                $itemPromotion = $item->currentPromotion();
                $itemHasPromo = $itemPromotion && $item->promo_price !== null && $item->promo_price < $item->price;
                $itemFinalPrice = $item->final_price;
                $imageUrl = $img ? asset('storage/'.$img) : 'https://via.placeholder.com/150';
            @endphp
            <div class="product-card">
                <img src="{{ $imageUrl }}" alt="{{ $item->name }}">
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
