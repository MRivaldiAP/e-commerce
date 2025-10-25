@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
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
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/palette.blade.php'), ['theme' => $themeName])->render() !!}
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

        .promo-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(254, 161, 22, 0.15);
            color: var(--theme-accent, #feA116);
            border-radius: 999px;
            padding: 0.2rem 0.75rem;
            font-size: 0.7rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .price-stack {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .price-stack.align-end {
            align-items: flex-end;
        }

        .price-stack .price-original {
            text-decoration: line-through;
            color: rgba(0, 0, 0, 0.45);
            font-size: 0.85rem;
        }

        .price-stack .price-current {
            color: var(--theme-accent, #feA116);
            font-weight: 700;
        }

        .order-summary-meta {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
@php
    use App\Support\LayoutSettings;

    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $items = $cartSummary['items'] ?? [];
    $instructions = $checkoutData['instructions'] ?? [];
    $publicConfig = $checkoutData['publicConfig'] ?? [];
    $selected = $selectedMethod ?? ($methods[0]['key'] ?? null);
    $feedbackMessage = $feedbackStatus['message'] ?? null;
    $feedbackType = $feedbackStatus['type'] ?? null;
@endphp

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

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
                            <div class="list-group-item d-flex justify-content-between align-items-start px-0 gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="mb-0">{{ $item['name'] }}</h6>
                                        @if(!empty($item['has_promo']) && !empty($item['promo_label']))
                                            <span class="promo-label">{{ $item['promo_label'] }}</span>
                                        @endif
                                    </div>
                                    <div class="price-stack">
                                        @if(!empty($item['has_promo']))
                                            <span class="price-original">Rp {{ $item['original_price_formatted'] }}</span>
                                        @endif
                                        <span class="price-current">Rp {{ $item['price_formatted'] }}</span>
                                    </div>
                                    <small class="text-muted">Jumlah: x{{ $item['quantity'] }}</small>
                                </div>
                                <div class="price-stack align-end">
                                    @if(!empty($item['has_promo']))
                                        <span class="price-original">Rp {{ $item['original_subtotal_formatted'] }}</span>
                                    @endif
                                    <span class="price-current">Rp {{ $item['subtotal_formatted'] }}</span>
                                </div>
                            </div>
                        @endforeach
                        @if($shippingEnabled)
                            <div class="list-group-item d-flex justify-content-between align-items-start px-0">
                                <div>
                                    <h6 class="mb-1">Ongkos Kirim</h6>
                                    @if(!empty($shippingData['selection']))
                                        <small class="text-muted">{{ strtoupper($shippingData['selection']['courier'] ?? '') }} • {{ $shippingData['selection']['service'] ?? '' }}</small>
                                    @endif
                                </div>
                                <span class="fw-semibold">Rp {{ $cartSummary['shipping_cost_formatted'] ?? '0' }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 order-summary-meta {{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-summary-original>
                            <span>Harga Normal</span>
                            <span>Rp {{ $cartSummary['original_total_formatted'] ?? '0' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 order-summary-meta text-success {{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-summary-discount>
                            <span>Diskon Promo</span>
                            <span>-Rp {{ $cartSummary['discount_total_formatted'] ?? '0' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 text-muted">
                            <span>Subtotal</span>
                            <span>Rp {{ $checkoutTotals['subtotal_formatted'] ?? ($cartSummary['total_price_formatted'] ?? '0') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 text-muted">
                            <span>Ongkir</span>
                            <span>Rp {{ $checkoutTotals['shipping_cost_formatted'] ?? '0' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-semibold">Total</span>
                            <span class="fs-4 fw-bold text-primary">Rp {{ $checkoutTotals['grand_total_formatted'] ?? ($cartSummary['total_price_formatted'] ?? '0') }}</span>
                        </div>
                    </div>
                    @if(!empty($shippingData))
                        <div class="bg-light rounded-3 p-3 mt-3">
                            <h6 class="fw-semibold mb-2 text-primary">Alamat Pengiriman</h6>
                            <div class="text-muted small">
                                <div>{{ $shippingData['contact']['name'] ?? '-' }} &bull; {{ $shippingData['contact']['phone'] ?? '-' }}</div>
                                <div>{{ $shippingData['address']['street'] ?? '-' }}</div>
                                <div>{{ $shippingData['address']['village_name'] ?? '' }}, {{ $shippingData['address']['district_name'] ?? '' }}</div>
                                <div>{{ $shippingData['address']['regency_name'] ?? '' }}, {{ $shippingData['address']['province_name'] ?? '' }} {{ $shippingData['address']['postal_code'] ?? '' }}</div>
                            </div>
                        </div>
                    @endif
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
                        @if($shippingEnabled && !empty($shippingData['selection']))
                            <div><strong>Pengiriman:</strong> {{ strtoupper($shippingData['selection']['courier'] ?? '') }} - {{ $shippingData['selection']['service'] ?? '' }} (Rp {{ $cartSummary['shipping_cost_formatted'] ?? '0' }})</div>
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

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

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

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
