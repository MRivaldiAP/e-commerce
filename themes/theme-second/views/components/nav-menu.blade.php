@php
    $cartSummary = $cart ?? ['total_quantity' => 0, 'total_price_formatted' => '0'];
    $brand = $brand ?? ['visible' => true, 'label' => 'Ogani Store', 'logo' => null, 'url' => url('/')];
    $links = $links ?? [];
    $showCart = $showCart ?? true;
    $showLogin = $showLogin ?? false;
    $showIcons = $showCart || $showLogin;
@endphp
<header id="navigation" class="header">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-6 col-lg-2 d-flex align-items-center">
                <div class="header__logo">
                    @if ($brand['visible'])
                        <a href="{{ $brand['url'] ?? url('/') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                            @if (!empty($brand['logo']))
                                <img src="{{ $brand['logo'] }}" alt="{{ $brand['label'] }}" style="max-height: 48px; width: auto;">
                            @else
                                <span class="fw-bold fs-4 text-success">{{ $brand['label'] }}</span>
                            @endif
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <nav class="header__menu justify-content-lg-center">
                    <ul>
                        @foreach($links as $link)
                            @if($link['visible'])
                                <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>
            <div class="col-6 col-lg-3">
                @if ($showIcons)
                    <div class="header__cart d-flex justify-content-end align-items-center">
                        @if ($showLogin)
                            <div class="header__cart__login">
                                @auth
                                    <a href="{{ route('orders.index') }}" class="text-decoration-none fw-semibold">Akun Saya</a>
                                @else
                                    <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Login</a>
                                @endauth
                            </div>
                        @endif
                        @if ($showCart)
                            <div>
                                <a href="{{ route('cart.index') }}" class="cart-indicator d-inline-flex align-items-center text-decoration-none">
                                    <i class="fa fa-shopping-bag me-1"></i>
                                    <span class="cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
                                </a>
                                <div class="header__cart__price">item: <span data-cart-total>{{ $cartSummary['total_price_formatted'] ?? '0' }}</span></div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="humberger__open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>

@once
    <style>
        .header__menu {
            display: flex;
            justify-content: flex-start;
        }

        @media (min-width: 992px) {
            .header__menu {
                justify-content: center;
            }
        }

        .header__menu > ul {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0;
            flex-wrap: nowrap;
        }

        .header__menu > ul > li {
            display: inline-flex;
            align-items: center;
        }

        .header__menu > ul > li > a {
            white-space: nowrap;
            font-size: clamp(0.85rem, 0.82rem + 0.25vw, 0.95rem);
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            .header__menu > ul {
                flex-wrap: wrap;
                gap: 1rem;
            }
        }

        @media (min-width: 992px) and (max-width: 1199.98px) {
            .header__menu > ul {
                gap: 0.75rem;
            }

            .header__menu > ul > li > a {
                font-size: 0.9rem;
            }
        }

        .header__cart {
            gap: 1rem;
        }

        @media (min-width: 992px) {
            .header__cart {
                gap: 0.75rem;
            }
        }

        .cart-indicator {
            position: relative;
        }

        .header__cart .cart-count {
            min-width: 1.5rem;
            height: 1.5rem;
            border-radius: 999px;
            background: #7fad39;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.25rem;
        }

        .cart-count.cart-bounce {
            animation: cartBounce 0.6s ease;
        }

        @keyframes cartBounce {
            0%, 100% {
                transform: scale(1);
            }
            30% {
                transform: scale(1.2);
            }
            50% {
                transform: scale(0.92);
            }
        }
    </style>
    <script>
        (function () {
            const initialSummary = @json($cartSummary);

            function updateCartIndicator(summary, animate = true) {
                document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                    const next = summary && typeof summary.total_quantity !== 'undefined' ? summary.total_quantity : 0;
                    const prev = parseInt(el.dataset.count || '0', 10);
                    el.textContent = next;
                    el.dataset.count = next;
                    if (animate && prev !== next) {
                        el.classList.remove('cart-bounce');
                        void el.offsetWidth;
                        el.classList.add('cart-bounce');
                        setTimeout(function () {
                            el.classList.remove('cart-bounce');
                        }, 600);
                    }
                });

                document.querySelectorAll('[data-cart-total]').forEach(function (el) {
                    el.textContent = summary && summary.total_price_formatted ? summary.total_price_formatted : '0';
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                updateCartIndicator(initialSummary, false);
            });

            window.addEventListener('cart:updated', function (event) {
                updateCartIndicator(event.detail || {}, true);
            });
        })();
    </script>
@endonce
