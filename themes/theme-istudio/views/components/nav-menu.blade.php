@php
    $cartSummary = $cart ?? ['total_quantity' => 0, 'total_price_formatted' => '0'];
    $brand = $brand ?? ['visible' => true, 'label' => 'iSTUDIO', 'logo' => null, 'icon' => null, 'url' => url('/')];
    $links = $links ?? [];
    $showCart = $showCart ?? true;
    $showLogin = $showLogin ?? false;
    $showIcons = $showCart || $showLogin;
@endphp
<div class="container-fluid sticky-top">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom border-2 border-white py-3">
            @if ($brand['visible'] ?? false)
                <a href="{{ $brand['url'] ?? url('/') }}" class="navbar-brand d-flex align-items-center gap-2">
                    @if (!empty($brand['logo']))
                        <img src="{{ $brand['logo'] }}" alt="{{ $brand['label'] ?? 'Brand' }}" style="max-height: 48px; width: auto;">
                    @elseif (!empty($brand['icon']))
                        <i class="{{ $brand['icon'] }} text-primary fs-2"></i>
                        <span class="fw-bold fs-3 text-uppercase mb-0">{{ $brand['label'] ?? 'Brand' }}</span>
                    @else
                        <h1 class="mb-0 text-uppercase">{{ $brand['label'] ?? 'Brand' }}</h1>
                    @endif
                </a>
            @endif
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto align-items-lg-center">
                    @foreach ($links as $link)
                        @if ($link['visible'] ?? false)
                            <a href="{{ $link['href'] }}" class="nav-item nav-link{{ url()->current() === $link['href'] ? ' active' : '' }}">{{ $link['label'] }}</a>
                        @endif
                    @endforeach
                </div>
                @if ($showIcons)
                    <div class="d-flex align-items-center gap-3 ms-lg-4 mt-3 mt-lg-0">
                        @if ($showLogin)
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-user text-primary"></i>
                                @auth
                                    <span class="fw-semibold" data-user-name>{{ auth()->user()->name ?? 'Akun Saya' }}</span>
                                @else
                                    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Login</a>
                                @endauth
                            </div>
                        @endif
                        @if ($showCart)
                            <div class="cart-dropdown position-relative" data-cart-dropdown>
                                <button type="button" class="d-inline-flex align-items-center gap-2 text-decoration-none position-relative cart-toggle" data-cart-toggle aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-shopping-bag text-primary"></i>
                                    <span class="badge rounded-pill bg-primary" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">
                                        {{ $cartSummary['total_quantity'] ?? 0 }}
                                    </span>
                                </button>
                                <div class="cart-dropdown-menu shadow" data-cart-menu>
                                    <a href="{{ route('cart.index') }}" class="dropdown-item d-flex align-items-center gap-2">
                                        <i class="fa fa-shopping-cart text-primary"></i>
                                        <span>Keranjang</span>
                                    </a>
                                    <a href="{{ route('orders.index') }}" class="dropdown-item d-flex align-items-center gap-2">
                                        <i class="fa fa-receipt text-primary"></i>
                                        <span>Pesanan Saya</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </nav>
    </div>
</div>

@once
    <style>
        .navbar .nav-link {
            font-weight: 500;
            letter-spacing: normal;
            text-transform: none;
        }

        .navbar .nav-link.active,
        .navbar .nav-link:focus,
        .navbar .nav-link:hover {
            color: var(--bs-primary) !important;
        }

        .navbar .badge[data-cart-count] {
            min-width: 1.75rem;
            min-height: 1.75rem;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 0.75rem;
        }

        .navbar .badge.cart-bounce {
            animation: cartBounce 0.6s ease;
        }

        .cart-dropdown .cart-toggle {
            background: transparent;
            border: none;
            padding: 0;
            color: inherit;
        }

        .cart-dropdown .cart-toggle:focus {
            outline: 2px solid var(--bs-primary);
            outline-offset: 3px;
        }

        .cart-dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.75rem);
            min-width: 13rem;
            background: #fff;
            border-radius: 0.75rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 0.5rem 0;
            display: none;
            z-index: 1050;
        }

        .cart-dropdown-menu.show {
            display: block;
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

            function updateCart(summary, animate = true) {
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
            }

            document.addEventListener('DOMContentLoaded', function () {
                updateCart(initialSummary, false);
            });

            window.addEventListener('cart:updated', function (event) {
                updateCart(event.detail || {}, true);
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
