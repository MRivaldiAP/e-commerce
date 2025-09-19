@php
    $cartSummary = $cart ?? ['total_quantity' => 0, 'total_price_formatted' => '0'];
@endphp
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
    <a href="{{ url('/') }}" class="navbar-brand p-0">
        <h1 class="text-primary m-0"><i class="fa fa-utensils me-3"></i>Restoran</h1>
    </a>
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
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('cart.index') }}" class="btn btn-outline-light cart-indicator position-relative">
                <i class="bi bi-cart"></i>
                <span class="badge bg-primary rounded-pill cart-count" data-cart-count data-count="{{ $cartSummary['total_quantity'] ?? 0 }}">{{ $cartSummary['total_quantity'] ?? 0 }}</span>
            </a>
            <a href="#" class="btn btn-primary py-2 px-4">Book A Table</a>
        </div>
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
