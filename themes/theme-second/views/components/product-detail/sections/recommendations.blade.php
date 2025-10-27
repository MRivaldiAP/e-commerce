<section class="related-product">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title related__product__title">
                    <h2>{{ $settings['recommendations.heading'] ?? 'Produk Serupa' }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($recommendations as $item)
                @php
                    $img = optional($item->images->first())->path;
                    $imageUrl = $img ? asset('storage/' . $img) : asset('storage/themes/' . $theme . '/img/product/product-1.jpg');
                    $promotion = $item->currentPromotion();
                    $hasPromo = $promotion && $item->promo_price !== null && $item->promo_price < $item->price;
                    $finalPrice = $item->final_price;
                @endphp
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product__item">
                        <div class="product__item__pic set-bg" data-setbg="{{ $imageUrl }}">
                            @if($hasPromo && !empty($promotion->label))
                                <span class="product__item__badge">{{ $promotion->label }}</span>
                            @endif
                            <ul class="product__item__pic__hover">
                                <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                            </ul>
                        </div>
                        <div class="product__item__text">
                            <h6><a href="{{ route('products.show', $item) }}">{{ $item->name }}</a></h6>
                            @if($hasPromo)
                                <span class="price-original">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                <span class="price-discount">Rp {{ number_format($finalPrice, 0, ',', '.') }}</span>
                            @else
                                <span class="price-discount">Rp {{ number_format($finalPrice, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
