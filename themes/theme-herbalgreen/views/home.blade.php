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
    $settings = PageSetting::where('page', 'home')->pluck('value', 'key')->toArray();

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

<section id="hero" class="hero">
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

<section id="products" class="products">
    <h2>Products</h2>
    <div class="product-grid">
        @for ($i = 1; $i <= 5; $i++)
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Product {{ $i }}">
                <h3>Product {{ $i }}</h3>
                <p>Short description for product {{ $i }}.</p>
            </div>
        @endfor
    </div>
</section>

<section id="services" class="services">
    <h2>Our Services</h2>
    <ul>
        <li>Consultation</li>
        <li>Custom Blends</li>
        <li>Workshops</li>
    </ul>
</section>

<section id="testimonials" class="testimonials">
    <h2>Testimonials</h2>
    <div class="testimonial">
        <p>"Amazing quality products!"</p>
        <span>- Happy Customer</span>
    </div>
    <div class="testimonial">
        <p>"I feel more relaxed than ever."</p>
        <span>- Satisfied Client</span>
    </div>
</section>

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
