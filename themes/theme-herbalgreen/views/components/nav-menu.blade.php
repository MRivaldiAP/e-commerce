@php
    $cartSummary = $cart ?? ['total_quantity' => 0, 'total_price_formatted' => '0'];
@endphp
<header id="navigation" class="site-header">
    <div class="logo">TEA</div>
    <nav class="main-nav">
        <ul>
            @foreach ($links as $link)
                @if ($link['visible'])
                    <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                @endif
            @endforeach
        </ul>
    </nav>
    <div class="header-icons">
        <span>🔍</span>
        <span>👤</span>
        <a href="{{ route('cart.index') }}" class="cart-indicator" data-cart-link>
            <span class="icon">🛒</span>
            <span class="cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
        </a>
    </div>
</header>

@once
    <style>
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
