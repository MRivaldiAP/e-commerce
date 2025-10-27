@php
    $filters = $list['filters'] ?? [];
    $searchValue = $filters['search'] ?? request('search');
    $selectedCategory = $filters['category'] ?? request('category');
    $selectedSort = $filters['sort'] ?? request('sort');
    $categories = $filters['categories'] ?? collect();
    $products = $list['products'] ?? collect();
@endphp
<div class="container py-5">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari Produk" value="{{ $searchValue }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($selectedCategory == $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="">Urutkan</option>
                <option value="price_asc" @selected($selectedSort == 'price_asc')>Harga Terendah</option>
                <option value="price_desc" @selected($selectedSort == 'price_desc')>Harga Tertinggi</option>
                <option value="sold_desc" @selected($selectedSort == 'sold_desc')>Terjual Terbanyak</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
    <div class="row g-4">
        @forelse($products as $product)
            @php
                $imagePath = $product->image_url ?? optional($product->images->first())->path;
                $promotion = $product->currentPromotion();
                $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
                $finalPrice = $product->final_price;
            @endphp
            <div class="col-lg-6">
                <div class="d-flex align-items-center">
                    <img class="flex-shrink-0 img-fluid rounded" src="{{ $imagePath ? asset('storage/' . ltrim($imagePath, '/')) : asset('storage/themes/theme-restoran/img/menu-1.jpg') }}" alt="{{ $product->name }}" style="width: 80px;">
                    <div class="w-100 d-flex flex-column text-start ps-4">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-2 gap-2">
                            <div class="d-flex flex-column gap-1">
                                <span class="fw-semibold">{{ $product->name }}</span>
                                @if($hasPromo)
                                    <span class="promo-badge">{{ $promotion->label }}</span>
                                @endif
                            </div>
                            <div class="price-stack text-end">
                                @if($hasPromo)
                                    <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                @endif
                                <span class="price-current">Rp {{ number_format($finalPrice, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <small class="fst-italic">{{ $product->description }}</small>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-primary mt-2 align-self-start">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border mb-0">Produk belum tersedia.</div>
            </div>
        @endforelse
    </div>
    @if($products instanceof \Illuminate\Contracts\Pagination\Paginator || $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>
