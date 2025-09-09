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
<header class="menu-bar">
    <nav>
        <ul>
            <li><a href="#hero">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#products">Products</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#testimonials">Testimonials</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
</header>

<section id="hero" class="hero">
    <h1>Welcome to Herbal Green</h1>
    <p>Feel the calm of nature's touch.</p>
    <button class="cta" onclick="document.getElementById('products').scrollIntoView({behavior:'smooth'});">Shop Now</button>
</section>

<section id="about" class="about">
    <h2>About Us</h2>
    <p>We provide herbal products to bring balance and serenity.</p>
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

<footer>
    <p>&copy; {{ date('Y') }} Herbal Green</p>
</footer>
</body>
</html>
