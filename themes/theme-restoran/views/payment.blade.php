<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $checkoutData['title'] ?? 'Pembayaran' }}</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
    <style>
        .payment-method-card {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            transition: all .2s ease;
            cursor: pointer;
        }
        .payment-method-card.active {
            border-color: var(--bs-primary);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .payment-method-card input[type="radio"] {
            margin-top: .3rem;
        }
        .payment-feedback {
            min-height: 1.5rem;
            font-weight: 600;
        }
        .payment-feedback.success {
            color: #198754;
        }
        .payment-feedback.error {
            color: #dc3545;
        }
        .payment-feedback.info {
            color: #0d6efd;
        }
    </style>
</head>
<body>
@php
    $navLinks = [
        ['label' => 'Homepage', 'href' => url('/'), 'visible' => true],
        ['label' => 'Menu', 'href' => url('/produk'), 'visible' => true],
        ['label' => 'Keranjang', 'href' => url('/keranjang'), 'visible' => true],
    ];
    $footerLinks = [
        ['label' => 'Privacy Policy', 'href' => '#', 'visible' => true],
        ['label' => 'Terms & Conditions', 'href' => '#', 'visible' => true],
    ];
    $items = $cartSummary['items'] ?? [];
    $instructions = $checkoutData['instructions'] ?? [];
    $publicConfig = $checkoutData['publicConfig'] ?? [];
    $selected = $selectedMethod ?? ($methods[0]['key'] ?? null);
    $feedbackMessage = $feedbackStatus['message'] ?? null;
    $feedbackType = $feedbackStatus['type'] ?? null;
@endphp

{!! view()->file(base_path('themes/' . $theme . '/views/components/navbar.blade.php'), ['links' => $navLinks, 'cart' => $cartSummary])->render() !!}

<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-5 mb-3">{{ $checkoutData['title'] ?? 'Pembayaran' }}</h1>
            <p class="text-muted">{{ $checkoutData['subtitle'] ?? 'Selesaikan transaksi Anda dengan gateway pilihan.' }}</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="bg-white rounded-3 shadow-lg p-4">
                    <h4 class="mb-4">Ringkasan Pesanan</h4>
                    <div class="list-group list-group-flush mb-4">
                        @foreach($items as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-start px-0">
                                <div>
                                    <h6 class="mb-1">{{ $item['name'] }}</h6>
                                    <small class="text-muted">x{{ $item['quantity'] }} • Rp {{ $item['price_formatted'] }}</small>
                                </div>
                                <span class="fw-semibold">Rp {{ $item['subtotal_formatted'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="fs-5 fw-semibold">Total</span>
                        <span class="fs-4 fw-bold text-primary">Rp {{ $cartSummary['total_price_formatted'] ?? '0' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="bg-white rounded-3 shadow-lg p-4 h-100 d-flex flex-column">
                    <div class="mb-4">
                        <h4 class="mb-2">Metode Pembayaran {{ $gatewayLabel }}</h4>
                        <p class="text-muted mb-0">Pilih metode pembayaran yang tersedia melalui {{ $gatewayLabel }}.</p>
                    </div>
                    <div class="row gy-3 mb-4" data-method-list>
                        @foreach($methods as $method)
                            <div class="col-md-6">
                                <label class="payment-method-card w-100 {{ $selected === $method['key'] ? 'active' : '' }}" data-method-card data-method="{{ $method['key'] }}">
                                    <div class="d-flex gap-3">
                                        <input type="radio" name="payment_method" value="{{ $method['key'] }}" {{ $selected === $method['key'] ? 'checked' : '' }}>
                                        <div>
                                            <div class="fw-semibold">{{ $method['label'] }}</div>
                                            <small class="text-muted">{{ $method['description'] }}</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-3">Langkah Pembayaran</h5>
                        <ol class="text-muted">
                            @forelse($instructions as $step)
                                <li class="mb-2">{{ $step }}</li>
                            @empty
                                <li>Pilih metode pembayaran dan ikuti petunjuk yang diberikan.</li>
                            @endforelse
                        </ol>
                    </div>
                    <div class="alert alert-info d-flex flex-column gap-1">
                        <div><strong>Gateway:</strong> {{ ucfirst($gatewayKey) }}</div>
                        @if(!empty($publicConfig['environment']))
                            <div><strong>Mode:</strong> {{ ucfirst($publicConfig['environment']) }}</div>
                        @endif
                        @if(!empty($publicConfig['client_key']))
                            <div><strong>Client Key:</strong> {{ substr($publicConfig['client_key'], 0, 6) }}••••</div>
                        @endif
                        @if(!empty($publicConfig['va']))
                            <div><strong>Virtual Account:</strong> {{ $publicConfig['va'] }}</div>
                        @endif
                    </div>
                    <div class="mt-auto">
                        <button class="btn btn-primary btn-lg w-100" data-pay-button>Bayar dengan {{ $gatewayLabel }}</button>
                        <div class="payment-feedback mt-3 {{ $feedbackType === 'success' ? 'success' : ($feedbackType === 'error' ? 'error' : ($feedbackType === 'info' ? 'info' : '')) }}" data-payment-feedback data-initial-message="{{ $feedbackMessage ?? '' }}" data-initial-type="{{ $feedbackType ?? '' }}">{{ $feedbackMessage }}</div>
                        <a href="{{ route('cart.index') }}" class="d-inline-flex align-items-center gap-2 mt-2 text-decoration-none">
                            <i class="bi bi-arrow-left"></i>
                            Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), ['links' => $footerLinks])->render() !!}

<script src="{{ asset('storage/themes/theme-restoran/js/bootstrap.bundle.min.js') }}"></script>
<script>
    (function(){
        const methodCards = document.querySelectorAll('[data-method-card]');
        const payButton = document.querySelector('[data-pay-button]');
        const feedback = document.querySelector('[data-payment-feedback]');
        const gateway = @json($gatewayKey);
        const sessionEndpoint = @json(route('checkout.payment.session'));
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        const statusClasses = ['success', 'error', 'info'];

        function activate(card){
            methodCards.forEach(item => item.classList.remove('active'));
            card.classList.add('active');
            const input = card.querySelector('input[type="radio"]');
            if(input){
                input.checked = true;
            }
        }

        function setFeedback(message, type){
            if(!feedback){
                return;
            }
            feedback.textContent = message || '';
            statusClasses.forEach(cls => feedback.classList.remove(cls));
            if(type && statusClasses.includes(type)){
                feedback.classList.add(type);
            }
        }

        if(feedback){
            const initialMessage = feedback.dataset.initialMessage || '';
            const initialType = feedback.dataset.initialType || '';
            if(initialMessage){
                setFeedback(initialMessage, initialType);
            }
        }

        methodCards.forEach(card => {
            card.addEventListener('click', function(event){
                if(event.target.matches('input')) return;
                activate(card);
            });
            const radio = card.querySelector('input[type="radio"]');
            if(radio){
                radio.addEventListener('change', () => activate(card));
            }
        });

        if(payButton){
            payButton.dataset.originalText = payButton.textContent;
            payButton.addEventListener('click', async () => {
                const selected = document.querySelector('input[name="payment_method"]:checked');
                if(!selected){
                    setFeedback('Pilih metode pembayaran terlebih dahulu.', 'error');
                    return;
                }

                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                if(csrfToken){
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                try {
                    payButton.disabled = true;
                    payButton.textContent = 'Memproses...';
                    setFeedback('Mempersiapkan pembayaran ' + (gateway || '').toUpperCase() + '...', 'info');

                    const response = await fetch(sessionEndpoint, {
                        method: 'POST',
                        headers,
                        body: JSON.stringify({ payment_method: selected.value })
                    });

                    const payload = await response.json().catch(() => ({}));

                    if(!response.ok || !payload || payload.status !== 'ok'){
                        const message = (payload && payload.message) ? payload.message : 'Gagal memproses pembayaran.';
                        throw new Error(message);
                    }

                    const data = payload.data || {};
                    const redirectUrl = data.redirect_url || data.url || data.snap_url;

                    if(redirectUrl){
                        window.location.href = redirectUrl;
                        return;
                    }

                    throw new Error('Gateway tidak mengembalikan tautan pembayaran.');
                } catch (error) {
                    setFeedback(error.message || 'Gagal memproses pembayaran.', 'error');
                } finally {
                    payButton.disabled = false;
                    if(payButton.dataset.originalText){
                        payButton.textContent = payButton.dataset.originalText;
                    }
                }
            });
        }
    })();
</script>
</body>
</html>
