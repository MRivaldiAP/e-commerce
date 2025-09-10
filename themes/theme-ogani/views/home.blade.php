<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ogani</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/css/style.css') }}" type="text/css">
</head>
<body>
    <div class="header__top">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header__top__left">
                        <span>Free Shipping on all Orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="header__logo">
                        <a href="#"><img src="https://via.placeholder.com/100x40?text=Ogani" alt="Ogani Logo"></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <nav class="header__menu">
                        <ul>
                            <li class="active"><a href="#hero">Home</a></li>
                            <li><a href="#products">Shop</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3">
                    <div class="header__cart">
                        <ul>
                            <li><a href="#"><i class="fa fa-heart"></i> <span>0</span></a></li>
                            <li><a href="#"><i class="fa fa-shopping-bag"></i> <span>0</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="hero" id="hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hero__item set-bg" style="background-image: url('https://via.placeholder.com/1200x400');">
                        <div class="hero__text">
                            <span>Fresh & Organic</span>
                            <h2>Vegetables <br/>100% Organic</h2>
                            <p>Free Pickup and Delivery Available</p>
                            <a href="#products" class="primary-btn">SHOP NOW</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about spad" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="about__text">
                        <h2>About Us</h2>
                        <p>We deliver fresh organic products to keep you healthy.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="featured spad" id="products">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Featured Products</h2>
                    </div>
                </div>
            </div>
            <div class="row featured__filter">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="col-lg-3 col-md-4 col-sm-6 mix">
                        <div class="featured__item">
                            <div class="featured__item__pic set-bg" data-setbg="https://via.placeholder.com/200x200"></div>
                            <div class="featured__item__text">
                                <h6><a href="#">Product {{ $i }}</a></h6>
                                <h5>$10.00</h5>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section class="services spad" id="services">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="services__item">
                        <h4>Farm-to-table delivery</h4>
                        <p>Get fresh produce delivered directly to your door.</p>
                    </div>
                    <div class="services__item">
                        <h4>Custom produce boxes</h4>
                        <p>Create your own box with seasonal selections.</p>
                    </div>
                    <div class="services__item">
                        <h4>Seasonal recipes</h4>
                        <p>Cook delicious meals with our curated recipes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonial spad" id="testimonials">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="testimonial__item">
                        <p>"Amazing quality products!"</p>
                        <h6>Happy Customer</h6>
                    </div>
                    <div class="testimonial__item">
                        <p>"Freshest veggies in town."</p>
                        <h6>Satisfied Client</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-form spad" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Contact Us</h2>
                    <form>
                        <div class="row">
                            <div class="col-lg-6">
                                <input type="text" placeholder="Name" required>
                            </div>
                            <div class="col-lg-6">
                                <input type="email" placeholder="Email" required>
                            </div>
                            <div class="col-lg-12">
                                <textarea placeholder="Message" required></textarea>
                                <button type="submit" class="site-btn">SEND</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="map" id="map">
        <iframe src="" frameborder="0" style="border:0" allowfullscreen></iframe>
    </div>

    <script src="{{ asset('themes/' . $theme . '/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/mixitup.min.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('themes/' . $theme . '/js/main.js') }}"></script>
</body>
</html>
