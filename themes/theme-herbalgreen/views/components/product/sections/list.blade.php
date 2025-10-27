<section class="product-search">
    <form method="GET">
        <input type="text" name="search" placeholder="Cari Produk..." value="{{ request('search') }}">
        <select name="category">
            <option value="">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category') == $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="sort">
            <option value="">Urutkan</option>
            <option value="price_asc" @selected(request('sort') == 'price_asc')>Harga Terendah</option>
            <option value="price_desc" @selected(request('sort') == 'price_desc')>Harga Tertinggi</option>
            <option value="sold_desc" @selected(request('sort') == 'sold_desc')>Terjual Terbanyak</option>
        </select>
        <button type="submit">Filter</button>
    </form>
</section>
<section id="products" class="products">
    <div class="product-grid">
        @foreach($products as $product)
            @php
                $imagePath = optional($product->images->first())->path;
                $promotion = $product->currentPromotion();
                $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
                $finalPrice = $product->final_price;
            @endphp
            <div class="product-card">
                <img src="{{ $imagePath ? asset('storage/'.$imagePath) : 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}">
                @if($hasPromo)
                    <span class="promo-label">{{ $promotion->label }}</span>
                @endif
                <h3>{{ $product->name }}</h3>
                @if($hasPromo)
                    <span class="price-original">Rp {{ number_format($product->price,0,',','.') }}</span>
                    <span class="price-current">Rp {{ number_format($finalPrice,0,',','.') }}</span>
                @else
                    <span class="price-current">Rp {{ number_format($finalPrice,0,',','.') }}</span>
                @endif
                <a href="{{ route('products.show', $product) }}" class="btn">Detail</a>
            </div>
        @endforeach
    </div>
    <div class="pagination">
        {{ $products->links() }}
    </div>
</section>
