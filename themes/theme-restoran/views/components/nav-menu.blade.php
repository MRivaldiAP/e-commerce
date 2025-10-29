@php
    $cartSummary = $cart ?? ['total_quantity' => 0, 'total_price_formatted' => '0'];
    $brand = $brand ?? ['visible' => true, 'label' => 'Restoran', 'logo' => null, 'url' => url('/')];
    $links = $links ?? [];
    $showCart = $showCart ?? true;
    $showLogin = $showLogin ?? false;
    $showIcons = $showCart || $showLogin;
    $brandIcon = $brand['icon'] ?? null;
@endphp
<nav id="navigation" class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
    @if ($brand['visible'])
        <a href="{{ $brand['url'] ?? url('/') }}" class="navbar-brand p-0 d-flex align-items-center gap-2">
            @if (!empty($brandIcon))
                <span class="text-primary d-inline-flex align-items-center justify-content-center" style="font-size: 1.5rem;">
                    <i class="{{ $brandIcon }}"></i>
                </span>
            @endif
            @if (!empty($brand['logo']))
                <img src="{{ $brand['logo'] }}" alt="{{ $brand['label'] }}" class="img-fluid" style="max-height: 46px; width: auto;">
            @else
                <span class="text-primary fw-bold fs-3 mb-0">{{ $brand['label'] }}</span>
            @endif
        </a>
    @endif
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0 pe-4">
            @foreach($links as $link)
                @if($link['visible'])
                    <a href="{{ $link['href'] }}" class="nav-item nav-link">{{ $link['label'] }}</a>
                @endif
            @endforeach
        </div>
        @if ($showIcons)
            <div class="d-flex align-items-center gap-2">
                @if ($showLogin)
                    @auth
                        <span class="text-white fw-semibold" data-user-name>{{ auth()->user()->name ?? 'Akun Saya' }}</span>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    @endauth
                @endif
                @if ($showCart)
                    <div class="dropdown cart-dropdown" data-cart-dropdown>
                        <button type="button" class="btn btn-outline-light cart-indicator position-relative" data-cart-toggle aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-cart"></i>
                            <span class="badge bg-primary rounded-pill cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow cart-dropdown-menu" data-cart-menu>
                            <a href="{{ route('cart.index') }}" class="dropdown-item d-flex align-items-center gap-2">
                                <i class="bi bi-bag"></i>
                                <span>Keranjang</span>
                            </a>
                            <a href="{{ route('orders.index') }}" class="dropdown-item d-flex align-items-center gap-2">
                                <i class="bi bi-receipt"></i>
                                <span>Pesanan Saya</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</nav>

@once
    <style>
        .cart-indicator .cart-count {
            position: absolute;
            top: -0.4rem;
            right: -0.6rem;
            font-size: 0.75rem;
            min-width: 1.5rem;
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
            min-width: 12rem;
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
                    if (menu.classList.contains('show')) {
                        menu.classList.remove('show');
                    }
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
