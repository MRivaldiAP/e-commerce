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
        <div class="row">
            <div class="col-lg-3">
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
            <div class="col-lg-6">
                <nav class="header__menu">
                    <ul>
                        @foreach($links as $link)
                            @continue(($link['key'] ?? null) === 'orders')

                            @if($link['visible'])
                                <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>
            <div class="col-lg-3">
                @if ($showIcons)
                    <div class="header__cart d-flex justify-content-end align-items-center gap-3">
                        @if ($showLogin)
                            <div class="header__cart__login">
                                @auth
                                    <span class="fw-semibold" data-user-name>{{ auth()->user()->name ?? 'Akun Saya' }}</span>
                                @else
                                    <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Login</a>
                                @endauth
                            </div>
                        @endif
                        @if ($showCart)
                            <div class="header__cart__summary">
                                <div class="header__cart__dropdown" data-cart-dropdown>
                                    <button type="button" class="cart-indicator d-inline-flex align-items-center text-decoration-none" data-cart-toggle aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-shopping-bag me-1"></i>
                                        <span class="cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
                                    </button>
                                    <div class="header__cart__menu" data-cart-menu>
                                        <a href="{{ route('cart.index') }}" class="header__cart__menu-item">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span>Keranjang</span>
                                        </a>
                                        <a href="{{ route('orders.index') }}" class="header__cart__menu-item">
                                            <i class="fa fa-receipt"></i>
                                            <span>Pesanan Saya</span>
                                        </a>
                                    </div>
                                </div>
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
        .header__menu > ul {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            margin: 0;
            flex-wrap: nowrap;
        }

        .header__menu > ul > li {
            display: inline-flex;
            align-items: center;
        }

        .header__menu > ul > li > a {
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .header__menu > ul {
                flex-wrap: wrap;
                gap: 1rem;
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

        .header__cart__summary {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.35rem;
        }

        .header__cart__dropdown {
            position: relative;
        }

        .header__cart__dropdown button.cart-indicator {
            background: none;
            border: none;
            padding: 0;
            color: inherit;
            cursor: pointer;
        }

        .header__cart__dropdown button.cart-indicator:focus {
            outline: 2px solid #7fad39;
            outline-offset: 2px;
        }

        .header__cart__menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            background: #fff;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: none;
            min-width: 12rem;
            padding: 0.5rem 0;
            z-index: 30;
        }

        .header__cart__menu.show {
            display: block;
        }

        .header__cart__menu-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: inherit;
            text-decoration: none;
            font-weight: 500;
        }

        .header__cart__menu-item:hover,
        .header__cart__menu-item:focus {
            background: rgba(127, 173, 57, 0.1);
            color: #7fad39;
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

            document.querySelectorAll('[data-cart-dropdown]').forEach(function (dropdown) {
                const toggle = dropdown.querySelector('[data-cart-toggle]');
                const menu = dropdown.querySelector('[data-cart-menu]');

                if (!toggle || !menu) {
                    return;
                }

                function closeMenu() {
                    menu.classList.remove('show');
                    menu.setAttribute('aria-hidden', 'true');
                    toggle.setAttribute('aria-expanded', 'false');
                }

                function openMenu() {
                    menu.classList.add('show');
                    menu.setAttribute('aria-hidden', 'false');
                    toggle.setAttribute('aria-expanded', 'true');
                }

                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (menu.classList.contains('show')) {
                        closeMenu();
                    } else {
                        document.querySelectorAll('[data-cart-menu].show').forEach(function (openMenuEl) {
                            openMenuEl.classList.remove('show');
                            openMenuEl.setAttribute('aria-hidden', 'true');
                            const relatedToggle = openMenuEl.closest('[data-cart-dropdown]')?.querySelector('[data-cart-toggle]');
                            if (relatedToggle) {
                                relatedToggle.setAttribute('aria-expanded', 'false');
                            }
                        });
                        openMenu();
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!dropdown.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeMenu();
                    }
                });

                closeMenu();
            });
        })();
    </script>
@endonce
