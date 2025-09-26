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
                    <a href="{{ route('orders.index') }}" class="login-link">Akun Saya</a>
                @else
                    <a href="{{ route('login') }}" class="login-link">Login</a>
                @endauth
            @endif
            @if ($showCart)
                <a href="{{ route('cart.index') }}" class="cart-indicator" data-cart-link>
                    <span class="icon">🛒</span>
                    <span class="cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
                </a>
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
