@php
    $commentsEnabled = ($settings['comments.visible'] ?? '1') === '1' && ($showCommentsSection ?? true);
@endphp
<section class="product-details spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="product__details__pic">
                    <div class="product__details__pic__item">
                        <img class="product__details__pic__item--large" src="{{ $images->first() }}" alt="{{ $product->name }}" id="mainProductImage">
                    </div>
                    <div class="product__details__pic__slider owl-carousel">
                        @foreach($images as $src)
                            <img data-imgbigurl="{{ $src }}" src="{{ $src }}" alt="{{ $product->name }} thumbnail">
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="product__details__text">
                    <h3>{{ $product->name }}</h3>
                    <div class="product__details__price">
                        @if($productHasPromo)
                            <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <span class="price-current">Rp {{ number_format($productFinalPrice, 0, ',', '.') }}<span class="promo-pill">{{ $productPromotion->label }}</span></span>
                        @else
                            <span class="price-current">Rp {{ number_format($productFinalPrice, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <p>{{ $product->short_description ?? 'Produk pilihan terbaik untuk memenuhi kebutuhan Anda setiap hari.' }}</p>
                    <div class="product__details__quantity">
                        <div class="quantity">
                            <div class="pro-qty">
                                <input type="text" value="1" id="quantityInput">
                            </div>
                        </div>
                    </div>
                    <a href="#" class="primary-btn" id="addToCartButton">MASUKKAN KE KERANJANG</a>
                    <a href="#" class="heart-icon"><span class="icon_heart_alt"></span></a>
                    <div class="cart-feedback" id="cartFeedback" role="status" aria-live="polite"></div>
                    <ul>
                        <li><b>Ketersediaan</b> <span>{{ $product->stock > 0 ? 'Stok Tersedia' : 'Stok Habis' }}</span></li>
                        <li><b>Berat</b> <span>{{ $product->weight ? $product->weight . ' kg' : '-' }}</span></li>
                        <li><b>Bagikan</b>
                            <div class="share">
                                <a href="#"><i class="fa fa-facebook"></i></a>
                                <a href="#"><i class="fa fa-twitter"></i></a>
                                <a href="#"><i class="fa fa-instagram"></i></a>
                                <a href="#"><i class="fa fa-pinterest"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="product__details__tab">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab" aria-selected="true">Deskripsi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab" aria-selected="false">Informasi</a>
                        </li>
                        @if($commentsEnabled)
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab" aria-selected="false">Komentar <span>({{ $comments->count() }})</span></a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tabs-1" role="tabpanel">
                            <div class="product__details__tab__desc">
                                {!! $product->description ? nl2br(e($product->description)) : '<p>Belum ada deskripsi produk.</p>' !!}
                            </div>
                        </div>
                        <div class="tab-pane" id="tabs-2" role="tabpanel">
                            <div class="product__details__tab__desc">
                                <ul class="list-unstyled">
                                    <li><strong>Kategori:</strong> {{ $product->categories->pluck('name')->join(', ') ?: '-' }}</li>
                                    <li><strong>Merek:</strong> {{ $product->brand?->name ?? '-' }}</li>
                                    <li><strong>SKU:</strong> {{ $product->sku ?? '-' }}</li>
                                    <li><strong>Dimensi:</strong> {{ $product->length }} x {{ $product->width }} x {{ $product->height }}</li>
                                </ul>
                            </div>
                        </div>
                        @if($commentsEnabled)
                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    @if($comments->isEmpty())
                                        <p>Belum ada komentar.</p>
                                    @else
                                        @foreach($comments as $comment)
                                            <div class="mb-4">
                                                <h6 class="mb-1">{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</h6>
                                                <small class="text-muted d-block mb-2">{{ optional($comment->created_at)->format('d M Y') }}</small>
                                                <p class="mb-0">{{ $comment->content }}</p>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
