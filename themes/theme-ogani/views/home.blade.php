<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ogani</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
</head>
<body>
<div class="shipping-bar">Fresh Produce Delivered</div>
<header class="site-header">
    <div class="logo">OGANI</div>
    <nav class="main-nav">
        <ul>
            <li><a href="#hero">Home</a></li>
            <li><a href="#products">Shop</a></li>
            <li><a href="#testimonials">Blog</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
    <div class="header-icons">
        <span>🔍</span>
        <span>👤</span>
        <span>🛒</span>
    </div>
</header>

<section id="hero" class="hero">
    <div class="hero-content">
        <span class="tagline">Fresh & Organic</span>
        <h1>Your Daily Groceries</h1>
        <p>Get high-quality organic produce every day.</p>
        <a href="#products" class="cta">Shop Now</a>
    </div>
</section>

<section id="about" class="about">
    <h2>About Us</h2>
    <p>We deliver fresh organic products to keep you healthy.</p>
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
        <li>Farm-to-table delivery</li>
        <li>Custom produce boxes</li>
        <li>Seasonal recipes</li>
    </ul>
</section>

<section id="testimonials" class="testimonials">
    <h2>Testimonials</h2>
    <div class="testimonial">
        <p>"Amazing quality products!"</p>
        <span>- Happy Customer</span>
    </div>
    <div class="testimonial">
        <p>"Freshest veggies in town."</p>
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
    <p>&copy; {{ date('Y') }} Ogani</p>
</footer>
<script src="{{ asset('themes/' . $theme . '/js/jquery.min.js') }}"></script>
<script src="{{ asset('themes/' . $theme . '/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('themes/' . $theme . '/js/main.js') }}"></script>
</body>
</html>
