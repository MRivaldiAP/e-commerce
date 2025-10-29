@php
    $cartSummary = $cart ?? ['total_quantity' => 0, 'total_price_formatted' => '0'];
    $brand = $brand ?? ['visible' => true, 'label' => 'TEA', 'logo' => null, 'url' => url('/')];
    $links = $links ?? [];
    $showCart = $showCart ?? true;
    $showLogin = $showLogin ?? false;
    $showIcons = $showCart || $showLogin;
@endphp
<header id="navigation" class="site-header">
    @if ($brand['visible'])
        <a href="{{ $brand['url'] ?? url('/') }}" class="logo">
            @if (!empty($brand['logo']))
                <img src="{{ $brand['logo'] }}" alt="{{ $brand['label'] }}" />
            @else
                <span>{{ $brand['label'] }}</span>
            @endif
        </a>
    @endif
    <nav class="main-nav">
        <ul>
            @foreach ($links as $link)
                @if ($link['visible'])
                    <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                @endif
            @endforeach
        </ul>
    </nav>
    @if ($showIcons)
        <div class="header-icons">
            @if ($showLogin)
                @auth
                    <span class="login-link" data-user-name>{{ auth()->user()->name ?? 'Akun Saya' }}</span>
                @else
                    <a href="{{ route('login') }}" class="login-link">Login</a>
                @endauth
            @endif
            @if ($showCart)
                <div class="cart-dropdown" data-cart-dropdown>
                    <button type="button" class="cart-indicator" data-cart-toggle aria-haspopup="true" aria-expanded="false">
                        <span class="icon">🛒</span>
                        <span class="cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
                    </button>
                    <div class="cart-dropdown-menu" data-cart-menu>
                        <a href="{{ route('cart.index') }}" class="cart-dropdown-item">
                            <span class="cart-dropdown-icon">🛍️</span>
                            <span>Keranjang</span>
                        </a>
                        <a href="{{ route('orders.index') }}" class="cart-dropdown-item">
                            <span class="cart-dropdown-icon">📦</span>
                            <span>Pesanan Saya</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</header>

@once
    <style>
        .header-icons {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.15rem;
            text-decoration: none;
            color: inherit;
        }

        .logo img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .login-link {
            text-decoration: none;
            font-weight: 600;
            color: inherit;
        }

        .cart-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
            color: inherit;
            position: relative;
            background: transparent;
            border: none;
            cursor: pointer;
            font: inherit;
            padding: 0;
        }

        .cart-count {
            min-width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            background: var(--color-primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
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

        .cart-dropdown {
            position: relative;
        }

        .cart-dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.75rem);
            background: #fff;
            color: #0f172a;
            border-radius: 0.75rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
            min-width: 13rem;
            padding: 0.5rem 0;
            display: none;
            z-index: 40;
        }

        .cart-dropdown-menu::before {
            content: "";
            position: absolute;
            right: 1.5rem;
            top: -0.5rem;
            width: 1rem;
            height: 1rem;
            background: #fff;
            transform: rotate(45deg);
            box-shadow: -3px -3px 10px rgba(15, 23, 42, 0.05);
        }

        .cart-dropdown-menu.show {
            display: block;
        }

        .cart-dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

        .cart-dropdown-item:hover,
        .cart-dropdown-item:focus {
            background: rgba(0, 0, 0, 0.03);
        }

        .cart-dropdown-icon {
            font-size: 1.2rem;
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
