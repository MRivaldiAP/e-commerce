<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herbal Green</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    $settings = PageSetting::forPage('home');
    $products = Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    $services = json_decode($settings['services.items'] ?? '[]', true);

    $navigation = LayoutSettings::navigation($theme);
    $footerConfig = LayoutSettings::footer($theme);
    $cartSummary = Cart::summary();
@endphp
{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="hero" @if(!empty($settings['hero.image'])) style="background-image:url('{{ asset('storage/'.$settings['hero.image']) }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.tagline'] ?? 'Go Natural' }}</span>
        <h1>{{ $settings['hero.heading'] ?? 'The Best Time to Drink Tea' }}</h1>
        <p>{{ $settings['hero.description'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}</p>
        <a href="{{ $settings['hero.button_link'] ?? '#products' }}" class="cta">{{ $settings['hero.button_label'] ?? 'Shop Now' }}</a>
    </div>
</section>
@endif

@if(($settings['about.visible'] ?? '1') == '1')
<section id="about" class="about">
    <h2>{{ $settings['about.heading'] ?? 'About Us' }}</h2>
    <p>{{ $settings['about.text'] ?? 'We provide herbal products to bring balance and serenity.' }}</p>
</section>
@endif

@if(($settings['products.visible'] ?? '1') == '1')
<section id="products" class="products">
    <h2>{{ $settings['products.heading'] ?? 'Products' }}</h2>
    <div class="product-grid">
        @foreach ($products as $product)
            <div class="product-card">
                @php $img = optional($product->images()->first())->path; @endphp
                <img src="{{ $img ? asset('storage/'.$img) : 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}">
                <h3>{{ $product->name }}</h3>
                <p>{{ $product->description }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

@if(($settings['services.visible'] ?? '1') == '1')
<section id="services" class="services">
    <h2>{{ $settings['services.heading'] ?? 'Our Services' }}</h2>
    @if(count($services))
    <ul>
        @foreach($services as $svc)
            <li>{{ $svc['title'] ?? '' }}</li>
        @endforeach
    </ul>
    @endif
</section>
@endif

@if(($settings['testimonials.visible'] ?? '1') == '1' && count($testimonials))
<section id="testimonials" class="testimonials">
    <h2>Testimonials</h2>
    @foreach($testimonials as $t)
    <div class="testimonial">
        <p>"{{ $t['text'] ?? '' }}"</p>
        <span>- {{ $t['title'] ?? '' }} {{ $t['name'] ?? '' }}</span>
    </div>
    @endforeach
</section>
@endif

@if(($settings['contact.visible'] ?? '1') == '1')
<section id="contact" class="contact">
    <h2>{{ $settings['contact.heading'] ?? 'Contact' }}</h2>
    <form>
        <input type="text" placeholder="Name" required>
        <input type="email" placeholder="Email" required>
        <textarea placeholder="Message" required></textarea>
        <button type="submit">Send</button>
    </form>
    @if(!empty($settings['contact.map']))
    <div class="map-container">{!! $settings['contact.map'] !!}</div>
    @endif
</section>
@endif

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
</body>
</html>
