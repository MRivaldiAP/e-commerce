<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $checkoutData['title'] ?? 'Pembayaran' }}</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        body { background: #f8f9fa; }
        .checkout__section { padding: 4rem 0; }
        .checkout__box { background: #fff; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); padding: 2rem; height: 100%; }
        .checkout__box h4 { font-weight: 600; margin-bottom: 1.5rem; }
        .summary__item { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
        .summary__item h6 { margin: 0 0 .35rem; font-weight: 600; }
        .summary__item span { font-weight: 600; color: #7fad39; }
        .summary__total { border-top: 1px solid #f2f2f2; padding-top: 1.5rem; display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; }
        .method__card { border: 1px solid #ececec; border-radius: 12px; padding: 1rem 1.25rem; display: flex; gap: .75rem; cursor: pointer; transition: all .2s ease; }
        .method__card.active { border-color: #7fad39; box-shadow: 0 10px 25px rgba(127,173,57,0.25); }
        .method__card input { margin-top: .3rem; }
        .instructions__list { padding-left: 1.25rem; color: #666; }
        .info__badge { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 1rem 1.25rem; font-size: .95rem; color: #166534; }
        .btn-pay { background: #7fad39; border: none; border-radius: 999px; padding: .9rem 2.5rem; color: #fff; font-weight: 600; width: 100%; }
        .btn-pay:hover { background: #6c9b30; }
        .payment-feedback { min-height: 1.5rem; margin-top: 1rem; font-weight: 600; color: #6c757d; }
        .payment-feedback.success { color: #7fad39; }
        .payment-feedback.error { color: #d9534f; }
        .payment-feedback.info { color: #0d6efd; }
    </style>
</head>
<body>
@php
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-second';
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

<section class="checkout__section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">{{ $checkoutData['title'] ?? 'Pembayaran' }}</h2>
            <p class="text-muted">{{ $checkoutData['subtitle'] ?? 'Pilih metode pembayaran terbaik dan selesaikan pesanan Anda.' }}</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="checkout__box">
                    <h4>Ringkasan Pesanan</h4>
                    @foreach($items as $item)
                        <div class="summary__item">
                            <div>
                                <h6>{{ $item['name'] }}</h6>
                                <small class="text-muted">x{{ $item['quantity'] }} • Rp {{ $item['price_formatted'] }}</small>
                            </div>
                            <span>Rp {{ $item['subtotal_formatted'] }}</span>
                        </div>
                    @endforeach
                    @if($shippingEnabled)
                        <div class="summary__item">
                            <div>
                                <h6>Ongkos Kirim</h6>
                                @if(!empty($shippingData['selection']))
                                    <small class="text-muted">{{ strtoupper($shippingData['selection']['courier'] ?? '') }} • {{ $shippingData['selection']['service'] ?? '' }}</small>
                                @endif
                            </div>
                            <span>Rp {{ $cartSummary['shipping_cost_formatted'] ?? '0' }}</span>
                        </div>
                    @endif
                    <div class="summary__total">
                        <span>Total Pembayaran</span>
                        <span>Rp {{ $cartSummary['grand_total_formatted'] ?? ($cartSummary['total_price_formatted'] ?? '0') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="checkout__box h-100 d-flex flex-column">
                    <h4 class="mb-3">Metode Pembayaran {{ $gatewayLabel }}</h4>
                    <div class="row g-3 mb-4" data-method-list>
                        @foreach($methods as $method)
                            <div class="col-md-6">
                                <label class="method__card {{ $selected === $method['key'] ? 'active' : '' }}" data-method-card data-method="{{ $method['key'] }}">
                                    <input type="radio" name="payment_method" value="{{ $method['key'] }}" {{ $selected === $method['key'] ? 'checked' : '' }}>
                                    <div>
                                        <div class="fw-semibold">{{ $method['label'] }}</div>
                                        <small class="text-muted">{{ $method['description'] }}</small>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-4">
                        <h5>Langkah Pembayaran</h5>
                        <ol class="instructions__list">
                            @forelse($instructions as $step)
                                <li class="mb-2">{{ $step }}</li>
                            @empty
                                <li>Pilih metode pembayaran dan ikuti panduan pada layar.</li>
                            @endforelse
                        </ol>
                    </div>
                    <div class="info__badge mb-4">
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
                        <button class="btn-pay" data-pay-button>Bayar dengan {{ $gatewayLabel }}</button>
                        <div class="payment-feedback {{ $feedbackType === 'success' ? 'success' : ($feedbackType === 'error' ? 'error' : ($feedbackType === 'info' ? 'info' : '')) }}" data-payment-feedback data-initial-message="{{ $feedbackMessage ?? '' }}" data-initial-type="{{ $feedbackType ?? '' }}">{{ $feedbackMessage }}</div>
                        <a href="{{ route('cart.index') }}" class="text-decoration-none text-muted">&larr; Kembali ke Keranjang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
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

        function setActive(card){
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
                setActive(card);
            });
            const radio = card.querySelector('input[type="radio"]');
            if(radio){
                radio.addEventListener('change', () => setActive(card));
            }
        });

        if(payButton){
            payButton.dataset.originalText = payButton.textContent;
            payButton.addEventListener('click', async function(){
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
                    setFeedback('Membuka pembayaran ' + (gateway || '').toUpperCase() + '...', 'info');

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
