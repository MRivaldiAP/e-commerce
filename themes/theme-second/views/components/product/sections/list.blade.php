<section class="product spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="hero__search">
                    <div class="hero__search__form">
                        <form method="GET">
                            <input type="text" name="search" placeholder="Cari Produk..." value="{{ $filters['search'] }}">
                            <input type="hidden" name="category" value="{{ $filters['category'] }}">
                            <input type="hidden" name="minprice" value="{{ $filters['minprice'] }}">
                            <input type="hidden" name="maxprice" value="{{ $filters['maxprice'] }}">
                            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                            <button type="submit" class="site-btn">SEARCH</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-5">
                <div class="sidebar">
                    <form method="GET" id="sidebar-form">
                        <input type="hidden" name="search" value="{{ $filters['search'] }}">
                        <input type="hidden" id="category" name="category" value="{{ $filters['category'] }}">
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                        <div class="sidebar__item">
                            <h4>Kategori</h4>
                            <ul>
                                <li class="{{ $filters['category'] ? '' : 'active' }}">
                                    <a href="#" onclick="event.preventDefault();document.getElementById('category').value='';document.getElementById('sidebar-form').submit();">Semua</a>
                                </li>
                                @foreach($categories as $category)
                                    <li class="{{ $filters['category'] === $category->slug ? 'active' : '' }}">
                                        <a href="#" onclick="event.preventDefault();document.getElementById('category').value='{{ $category->slug }}';document.getElementById('sidebar-form').submit();">{{ $category->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="sidebar__item">
                            <h4>Harga</h4>
                            <div class="price-range-wrap">
                                <div id="price-range" class="price-range"></div>
                                <div class="range-slider">
                                    <div class="price-input">
                                        <input type="text" id="minamount" name="minprice" value="{{ $filters['minprice'] ?? 0 }}">
                                        <input type="text" id="maxamount" name="maxprice" value="{{ $filters['maxprice'] ?? 1000000 }}">
                                    </div>
                                    <button type="submit" class="site-btn mt-3">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-9 col-md-7">
                <div class="filter__item">
                    <div class="row">
                        <div class="col-lg-4 col-md-5">
                            <div class="filter__sort">
                                <span>Sort By</span>
                                <form method="GET" id="sort-form">
                                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                    <input type="hidden" name="minprice" value="{{ $filters['minprice'] }}">
                                    <input type="hidden" name="maxprice" value="{{ $filters['maxprice'] }}">
                                    <select name="sort" onchange="document.getElementById('sort-form').submit()">
                                        <option value="">Default</option>
                                        <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Harga Terendah</option>
                                        <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Harga Tertinggi</option>
                                        <option value="sold_desc" @selected($filters['sort'] === 'sold_desc')>Terjual Terbanyak</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-7">
                            <div class="filter__found">
                                <h6><span>{{ $products->total() }}</span> Produk ditemukan</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @foreach($products as $product)
                        @php
                            $imagePath = $product->image_url ?? optional($product->images->first())->path;
                            $imageUrl = $imagePath ? asset('storage/' . $imagePath) : asset('storage/themes/' . $theme . '/img/product/product-1.jpg');
                            $promotion = $product->currentPromotion();
                            $hasPromo = $promotion && $product->promo_price !== null && $product->promo_price < $product->price;
                            $finalPrice = $product->final_price;
                        @endphp
                        <div class="col-lg-4 col-md-6 col-sm-6">
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
                                    <h6><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h6>
                                    @if($hasPromo)
                                        <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        <span class="price-discount">Rp {{ number_format($finalPrice, 0, ',', '.') }}</span>
                                    @else
                                        <span class="price-discount">Rp {{ number_format($finalPrice, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="product__pagination">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
