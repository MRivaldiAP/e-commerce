<section id="products" class="featured spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2>{{ $settings['products.heading'] ?? 'Featured Product' }}</h2>
                </div>
            </div>
        </div>
        <div class="row featured__filter">
            @foreach($products as $product)
                @php
                    $image = $product->image_url ?? optional($product->images()->first())->path;
                    $thumbnail = $image ? asset('storage/' . $image) : asset('storage/themes/' . $theme . '/img/featured/feature-1.jpg');
                @endphp
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="featured__item">
                        <div class="featured__item__pic set-bg" data-setbg="{{ $thumbnail }}">
                            <ul class="featured__item__pic__hover">
                                <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                            </ul>
                        </div>
                        <div class="featured__item__text">
                            <h6><a href="#">{{ $product->title }}</a></h6>
                            <h5>{{ $product->price_formatted ?? number_format($product->price, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
