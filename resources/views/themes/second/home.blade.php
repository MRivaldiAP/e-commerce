<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second Theme</title>
    <link rel="stylesheet" href="{{ asset('ogani-master/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('ogani-master/css/style.css') }}" type="text/css">
    <style>
        .header {position: sticky; top: 0; z-index: 1000; background: #fff;}
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    $settings = PageSetting::forPage('home');
    $products = Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    $services = json_decode($settings['services.items'] ?? '[]', true);
    $navLinks = [
        ['label' => 'Homepage', 'href' => '#hero', 'visible' => ($settings['navigation.home'] ?? '1') == '1'],
        ['label' => 'Tea Collection', 'href' => '#products', 'visible' => ($settings['navigation.products'] ?? '1') == '1'],
        ['label' => 'News', 'href' => '#testimonials', 'visible' => ($settings['navigation.news'] ?? '1') == '1'],
        ['label' => 'Contact Us', 'href' => '#contact', 'visible' => ($settings['navigation.contact'] ?? '1') == '1'],
    ];
    $aboutImage = $settings['about.image'] ?? null;
@endphp

{!! view()->file(base_path('themes/theme-second/views/components/nav-menu.blade.php'), ['links' => $navLinks])->render() !!}

@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="hero__item set-bg" data-setbg="{{ !empty($settings['hero.image']) ? asset('storage/'.$settings['hero.image']) : asset('ogani-master/img/hero/banner.jpg') }}">
                    <div class="hero__text">
                        <span>{{ $settings['hero.tagline'] ?? 'FRUIT FRESH' }}</span>
                        <h2>{{ $settings['hero.heading'] ?? 'Vegetable 100% Organic' }}</h2>
                        <p>{{ $settings['hero.description'] ?? 'Free Pickup and Delivery Available' }}</p>
                        <a href="{{ $settings['hero.button_link'] ?? route('products.index') }}" class="primary-btn">{{ $settings['hero.button_label'] ?? 'SHOP NOW' }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['about.visible'] ?? '1') == '1')
<section id="about" class="about spad">
    <div class="container">
        <div class="blog__details__author">
            <div class="blog__details__author__pic">
                <img src="{{ $aboutImage ? asset('storage/'.$aboutImage) : asset('ogani-master/img/blog/details/blog-author.jpg') }}" alt="">
            </div>
            <div class="blog__details__author__text">
                <h4>{{ $settings['about.heading'] ?? 'About Us' }}</h4>
                <p>{{ $settings['about.text'] ?? 'We provide quality products.' }}</p>
            </div>
        </div>
    </div>
</section>
@endif

@if(($settings['products.visible'] ?? '1') == '1')
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
            @php $img = $product->image_url ?? optional($product->images()->first())->path; @endphp
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="featured__item">
                    <div class="featured__item__pic set-bg" data-setbg="{{ $img ? asset('storage/'.$img) : asset('ogani-master/img/featured/feature-1.jpg') }}">
                        <ul class="featured__item__pic__hover">
                            <li><a href="#"><i class="fa fa-heart"></i></a></li>
                            <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                            <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                        </ul>
                    </div>
                    <div class="featured__item__text">
                        <h6><a href="{{ route('products.show', $product) }}">{{ $product->title }}</a></h6>
                        <h5>{{ $product->price_formatted ?? number_format($product->price, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($settings['services.visible'] ?? '1') == '1' && count($services))
<section id="services" class="services spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <h2>{{ $settings['services.heading'] ?? 'Our Services' }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($services as $svc)
            <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                <div class="contact__widget">
                    <span class="icon_check"></span>
                    <h4>{{ $svc['title'] ?? '' }}</h4>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($settings['testimonials.visible'] ?? '1') == '1' && count($testimonials))
<section id="testimonials" class="from-blog spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title from-blog__title">
                    <h2>Testimonials</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($testimonials as $t)
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="blog__item">
                    <div class="blog__item__pic">
                        <img src="{{ asset('ogani-master/img/blog/blog-' . (($loop->iteration - 1) % 3 + 1) . '.jpg') }}" alt="">
                    </div>
                    <div class="blog__item__text">
                        <ul>
                            <li><i class="fa fa-user"></i> {{ $t['name'] ?? '' }}</li>
                        </ul>
                        <h5><a href="#">{{ $t['title'] ?? '' }}</a></h5>
                        <p>{{ $t['text'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($settings['contact.visible'] ?? '1') == '1')
<section id="contact" class="contact-form spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="contact__form__title">
                    <h2>{{ $settings['contact.heading'] ?? 'Leave Message' }}</h2>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('contact.submit') }}">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <input type="text" name="name" placeholder="Your name" required>
                </div>
                <div class="col-lg-6 col-md-6">
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="col-lg-12">
                    <input type="text" name="subject" placeholder="Subject" required>
                </div>
                <div class="col-lg-12 text-center">
                    <textarea name="message" placeholder="Your message" required></textarea>
                    <button type="submit" class="site-btn">SEND MESSAGE</button>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="map">
    @if(!empty($settings['contact.map']))
        {!! $settings['contact.map'] !!}
    @else
        <div style="width:100%; height:500px; background:#f2f2f2;"></div>
    @endif
</div>
@endif

{!! view()->file(base_path('themes/theme-second/views/components/footer.blade.php'), ['settings' => $settings])->render() !!}

<script src="{{ asset('ogani-master/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('ogani-master/js/mixitup.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('ogani-master/js/main.js') }}"></script>
</body>
</html>

