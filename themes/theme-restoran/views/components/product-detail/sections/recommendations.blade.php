@if($recommendations['visible'] ?? false)
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title ff-secondary text-center text-primary fw-normal">{{ $recommendations['heading'] ?? 'Produk Serupa' }}</h2>
    </div>
    <div class="row g-4">
        @foreach($recommendations['items'] ?? [] as $item)
            <div class="col-lg-6">
                <div class="d-flex align-items-center recommendation-card">
                    <img class="flex-shrink-0 img-fluid rounded" src="{{ $item['image'] ?? asset('storage/themes/theme-restoran/img/menu-1.jpg') }}" alt="{{ $item['name'] ?? 'Produk' }}" style="width: 80px;">
                    <div class="w-100 d-flex flex-column text-start ps-4">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-2 gap-2">
                            <div class="d-flex flex-column gap-1">
                                <span>{{ $item['name'] ?? 'Produk' }}</span>
                                @if($item['has_promo'] ?? false)
                                    <span class="promo-badge">{{ $item['promotion_label'] }}</span>
                                @endif
                            </div>
                            <div class="price-stack text-end">
                                @if(($item['has_promo'] ?? false) && !empty($item['price_original']))
                                    <span class="price-original">Rp {{ number_format($item['price_original'], 0, ',', '.') }}</span>
                                @endif
                                <span class="price-current">Rp {{ number_format($item['price_current'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <small class="fst-italic">{{ $item['description'] ?? '' }}</small>
                        <a href="{{ $item['url'] ?? '#' }}" class="btn btn-sm btn-primary mt-2 align-self-start">Detail</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
