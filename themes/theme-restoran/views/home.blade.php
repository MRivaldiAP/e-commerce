@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Restoran Theme</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/palette.blade.php'), ['theme' => $themeName])->render() !!}
    <style>
        .navbar {position: sticky; top:0; z-index:1030;}
        .navbar-dark{background:var(--dark)!important;}
        .hero-header img.main{animation:none;}
        .hero-header img.spin{animation:imgRotate 50s linear infinite!important;}
        .hero-header .spin-text{animation:imgRotate 50s linear infinite;}
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    $settings = PageSetting::forPage('home');
    $products = Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    $services = json_decode($settings['services.items'] ?? '[]', true);
    $aboutImage = $settings['about.image'] ?? null;
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    $heroImage = ThemeMedia::url($settings['hero.image'] ?? null);
    $heroSpinImage = ThemeMedia::url($settings['hero.spin_image'] ?? null);
    if ($heroImage) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(var(--theme-accent-rgb), 0.9), rgba(var(--theme-accent-rgb), 0.9)), url('{$heroImage}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroImage}'); background-size: cover; background-position: center;";
        }
    } elseif (! $heroMaskEnabled) {
        $heroStyle = 'background-image: none;';
    }
@endphp
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    @if(($settings['hero.visible'] ?? '1') == '1')
    <div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
        <div class="container my-5 py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 text-center text-lg-start">
                    @if(!empty($settings['hero.tagline']))
                    <span class="text-white">{{ $settings['hero.tagline'] }}</span>
                    @endif
                    <h1 class="display-3 text-white animated slideInLeft">{{ $settings['hero.heading'] ?? 'Enjoy Our Delicious Meal' }}</h1>
                    <p class="text-white animated slideInLeft mb-4 pb-2">{{ $settings['hero.description'] ?? 'Tempor erat elitr rebum at clita.' }}</p>
                    <a href="{{ $settings['hero.button_link'] ?? '#' }}" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">{{ $settings['hero.button_label'] ?? 'Book A Table' }}</a>
                </div>
                <div class="col-lg-6 text-center text-lg-end overflow-hidden position-relative">
                    <img class="img-fluid main spin" src="{{ $heroSpinImage ?: asset('storage/themes/theme-restoran/img/hero.png') }}" alt="">
                    @if(!empty($settings['hero.spin_text']))
                    <span class="text-white position-absolute top-50 start-50 translate-middle spin-text">{{ $settings['hero.spin_text'] }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@if(($settings['services.visible'] ?? '1') == '1' && count($services))
<div id="services" class="container-xxl py-5">
    <div class="container">
        <div class="row g-4">
            @foreach($services as $index => $svc)
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="{{ 0.1 + $index*0.2 }}s">
                <div class="service-item rounded pt-3">
                    <div class="p-4">
                        <i class="{{ $svc['icon'] ?? 'fa fa-3x fa-check text-primary mb-4' }}"></i>
                        <h5>{{ $svc['title'] ?? '' }}</h5>
                        <p>{{ $svc['text'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@if(($settings['about.visible'] ?? '1') == '1')
<div id="about" class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img class="img-fluid rounded w-100" src="{{ $aboutImage ? asset('storage/'.$aboutImage) : asset('storage/themes/theme-restoran/img/about-1.jpg') }}" alt="">
            </div>
            <div class="col-lg-6">
                <h5 class="section-title ff-secondary text-start text-primary fw-normal">About Us</h5>
                <h1 class="mb-4">{{ $settings['about.heading'] ?? 'Welcome to Restoran' }}</h1>
                <p class="mb-4">{{ $settings['about.text'] ?? 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit.' }}</p>
            </div>
        </div>
    </div>
</div>
@endif
@if(($settings['products.visible'] ?? '1') == '1')
<div id="products" class="container-xxl py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
            <h1 class="mb-5">{{ $settings['products.heading'] ?? 'Most Popular Items' }}</h1>
        </div>
        <div class="row g-4">
            @foreach($products as $product)
            @php $img = $product->image_url ?? optional($product->images()->first())->path; @endphp
            <div class="col-lg-6">
                <div class="d-flex align-items-center">
                    <img class="flex-shrink-0 img-fluid rounded" src="{{ $img ? asset('storage/'.$img) : asset('storage/themes/theme-restoran/img/menu-1.jpg') }}" alt="{{ $product->name }}" style="width: 80px;">
                    <div class="w-100 d-flex flex-column text-start ps-4">
                        <h5 class="d-flex justify-content-between border-bottom pb-2">
                            <span>{{ $product->name }}</span>
                            <span class="text-primary">{{ $product->price_formatted ?? number_format($product->price, 0, ',', '.') }}</span>
                        </h5>
                        <small class="fst-italic">{{ $product->description }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@if(($settings['testimonials.visible'] ?? '1') == '1' && count($testimonials))
<div id="testimonials" class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="text-center">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Testimonial</h5>
            <h1 class="mb-5">Our Clients Say!!!</h1>
        </div>
        <div class="owl-carousel testimonial-carousel">
            @foreach($testimonials as $t)
            <div class="testimonial-item bg-transparent border rounded p-4">
                <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
                <p>{{ $t['text'] ?? '' }}</p>
                <div class="d-flex align-items-center">
                    @php $photo = $t['photo'] ?? null; @endphp
                    <img class="img-fluid flex-shrink-0 rounded-circle" src="{{ $photo ? asset('storage/'.$photo) : asset('storage/themes/theme-restoran/img/testimonial-1.jpg') }}" style="width: 50px; height: 50px;">
                    <div class="ps-3">
                        <h5 class="mb-1">{{ $t['name'] ?? '' }}</h5>
                        <small>{{ $t['title'] ?? '' }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@if(($settings['contact.visible'] ?? '1') == '1')
<div id="contact" class="container-xxl py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Contact Us</h5>
            <h1 class="mb-5">{{ $settings['contact.heading'] ?? 'Contact For Any Query' }}</h1>
        </div>
        <div class="row g-4">
            <div class="col-md-6 wow fadeIn" data-wow-delay="0.1s">
                @if(!empty($settings['contact.map']))
                    {!! $settings['contact.map'] !!}
                @else
                    <div style="width:100%;height:100%;min-height:350px;background:#f2f2f2;"></div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="wow fadeInUp" data-wow-delay="0.2s">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" placeholder="Your Name">
                                    <label for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" placeholder="Your Email">
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" placeholder="Subject">
                                    <label for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Leave a message here" id="message" style="height: 150px"></textarea>
                                    <label for="message">Message</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3" type="submit">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
</body>
</html>
