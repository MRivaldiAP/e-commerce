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
    $settings = PageSetting::where('theme', $theme)->where('page', 'home')->pluck('value', 'key')->toArray();
    $products = Product::where('is_featured', true)->latest()->take(5)->get();
    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);

    $navLinks = [
        ['label' => 'Homepage', 'href' => '#hero', 'visible' => ($settings['navigation.home'] ?? '1') == '1'],
        ['label' => 'Tea Collection', 'href' => '#products', 'visible' => ($settings['navigation.products'] ?? '1') == '1'],
        ['label' => 'News', 'href' => '#testimonials', 'visible' => ($settings['navigation.news'] ?? '1') == '1'],
        ['label' => 'Contact Us', 'href' => '#contact', 'visible' => ($settings['navigation.contact'] ?? '1') == '1'],
    ];

    $footerLinks = [
        ['label' => 'Privacy Policy', 'href' => '#', 'visible' => ($settings['footer.privacy'] ?? '1') == '1'],
        ['label' => 'Terms & Conditions', 'href' => '#', 'visible' => ($settings['footer.terms'] ?? '1') == '1'],
    ];
@endphp
@if(($settings['topbar.visible'] ?? '1') == '1')
<div id="topbar" class="shipping-bar">{{ $settings['topbar.text'] ?? 'Free Worldwide Shipping' }}</div>
@endif
{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), ['links' => $navLinks])->render() !!}

<section id="hero" class="hero" @if(!empty($settings['hero.image'])) style="background-image:url('{{ asset('storage/'.$settings['hero.image']) }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.tagline'] ?? 'Go Natural' }}</span>
        <h1>{{ $settings['hero.heading'] ?? 'The Best Time to Drink Tea' }}</h1>
        <p>{{ $settings['hero.description'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}</p>
        <a href="{{ $settings['hero.button_link'] ?? '#products' }}" class="cta">{{ $settings['hero.button_label'] ?? 'Shop Now' }}</a>
    </div>
</section>

<section id="about" class="about">
    <h2>{{ $settings['about.heading'] ?? 'About Us' }}</h2>
    <p>{{ $settings['about.text'] ?? 'We provide herbal products to bring balance and serenity.' }}</p>
</section>

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

<section id="services" class="services">
    <h2>Our Services</h2>
    <ul>
        <li>Consultation</li>
        <li>Custom Blends</li>
        <li>Workshops</li>
    </ul>
</section>

@if(count($testimonials))
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

<section id="contact" class="contact">
    <h2>Contact</h2>
    <form>
        <input type="text" placeholder="Name" required>
        <input type="email" placeholder="Email" required>
        <textarea placeholder="Message" required></textarea>
        <button type="submit">Send</button>
    </form>
</section>

<section id="map" class="map">
    <h2>Find Us</h2>
    <div class="map-container">
        <!-- Map embed placeholder -->
    </div>
</section>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), ['links' => $footerLinks, 'copyright' => $settings['footer.copyright'] ?? ('© ' . date('Y') . ' Herbal Green')])->render() !!}
</body>
</html>
