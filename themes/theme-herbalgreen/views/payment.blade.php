<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $checkoutData['title'] ?? 'Pembayaran' }}</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <style>
        body {
            background: #f4f9f4;
        }
        #payment {
            padding: 4rem 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }
        .payment-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .payment-header h1 {
            font-size: 2.25rem;
            margin-bottom: .75rem;
        }
        .payment-header p {
            color: #52796f;
            margin: 0;
        }
        .payment-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
            gap: 2rem;
        }
        .card-box {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        .summary-items {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .summary-item h4 {
            margin: 0 0 .25rem;
            font-size: 1.1rem;
        }
        .summary-item span {
            font-weight: 600;
            color: var(--color-primary);
        }
        .summary-total {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px dashed #cbd5f0;
            display: flex;
            justify-content: space-between;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .method-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .method-card {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            border: 1px solid #d8e2dc;
            border-radius: 14px;
            padding: 1rem;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .method-card input {
            margin-top: .2rem;
        }
        .method-card.active {
            border-color: var(--color-primary);
            box-shadow: 0 6px 18px rgba(53, 131, 116, 0.15);
        }
        .method-card small {
            color: #6b9080;
            display: block;
        }
        .payment-instructions {
            margin-bottom: 2rem;
        }
        .payment-instructions h3 {
            font-size: 1.15rem;
            margin-bottom: .75rem;
        }
        .payment-instructions ol {
            padding-left: 1.25rem;
            color: #4a4a4a;
        }
        .payment-meta {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-size: .95rem;
            color: #166534;
        }
        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: .95rem 2.75rem;
            background: var(--color-primary);
            color: #fff;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform .2s ease;
        }
        .cta-button:hover {
            transform: translateY(-1px);
        }
        .payment-feedback {
            margin-top: 1rem;
            min-height: 1.5rem;
            font-weight: 500;
            color: #d97706;
        }
        .payment-feedback.success {
            color: #047857;
        }
        .payment-feedback.error {
            color: #dc2626;
        }
        .payment-feedback.info {
            color: #2563eb;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-top: 1.5rem;
            color: var(--color-secondary);
            text-decoration: none;
        }
        @media (max-width: 992px) {
            .payment-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $navLinks = [
        ['label' => 'Homepage', 'href' => url('/'), 'visible' => true],
        ['label' => 'Produk', 'href' => url('/produk'), 'visible' => true],
        ['label' => 'Keranjang', 'href' => url('/keranjang'), 'visible' => true],
    ];
    $footerLinks = [
        ['label' => 'Privacy Policy', 'href' => '#', 'visible' => true],
        ['label' => 'Terms & Conditions', 'href' => '#', 'visible' => true],
    ];
    $summaryItems = $cartSummary['items'] ?? [];
    $instructions = $checkoutData['instructions'] ?? [];
    $subtitle = $checkoutData['subtitle'] ?? 'Selesaikan pembayaran Anda dengan aman.';
    $publicConfig = $checkoutData['publicConfig'] ?? [];
    $selected = $selectedMethod ?? ($methods[0]['key'] ?? null);
    $feedbackMessage = $feedbackStatus['message'] ?? null;
    $feedbackType = $feedbackStatus['type'] ?? null;
@endphp

{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), ['links' => $navLinks, 'cart' => $cartSummary])->render() !!}

<section id="payment">
    <div class="payment-header">
        <h1>{{ $checkoutData['title'] ?? 'Pembayaran' }}</h1>
        <p>{{ $subtitle }}</p>
    </div>
    <div class="payment-layout">
        <div class="card-box">
            <h3 class="mb-3">Ringkasan Pesanan</h3>
            <div class="summary-items">
                @foreach($summaryItems as $item)
                    <div class="summary-item">
                        <div>
                            <h4>{{ $item['name'] }}</h4>
                            <small>x{{ $item['quantity'] }} • Rp {{ $item['price_formatted'] }}</small>
                        </div>
                        <span>Rp {{ $item['subtotal_formatted'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="summary-total">
                <span>Total Pembayaran</span>
                <span>Rp {{ $cartSummary['total_price_formatted'] ?? '0' }}</span>
            </div>
        </div>
        <div class="card-box">
            <h3 class="mb-3">Metode Pembayaran {{ $gatewayLabel }}</h3>
            <div class="method-list" data-method-list>
                @foreach($methods as $method)
                    <label class="method-card {{ $selected === $method['key'] ? 'active' : '' }}" data-method-card data-method="{{ $method['key'] }}">
                        <input type="radio" name="payment_method" value="{{ $method['key'] }}" {{ $selected === $method['key'] ? 'checked' : '' }}>
                        <div>
                            <strong>{{ $method['label'] }}</strong>
                            <small>{{ $method['description'] }}</small>
                        </div>
                    </label>
                @endforeach
            </div>
            <div class="payment-instructions">
                <h3>Langkah Pembayaran</h3>
                <ol>
                    @forelse($instructions as $step)
                        <li>{{ $step }}</li>
                    @empty
                        <li>Pilih metode pembayaran dan lanjutkan sesuai instruksi yang muncul.</li>
                    @endforelse
                </ol>
            </div>
            <div class="payment-meta mb-3">
                <strong>Informasi Gateway:</strong>
                <div>Gateway aktif: {{ ucfirst($gatewayKey) }}</div>
                @if(!empty($publicConfig['environment']))
                    <div>Mode: {{ ucfirst($publicConfig['environment']) }}</div>
                @endif
                @if(!empty($publicConfig['client_key']))
                    <div>Client Key: {{ substr($publicConfig['client_key'], 0, 6) }}••••</div>
                @endif
                @if(!empty($publicConfig['va']))
                    <div>Virtual Account: {{ $publicConfig['va'] }}</div>
                @endif
            </div>
            <button class="cta-button" data-pay-button>Bayar dengan {{ $gatewayLabel }}</button>
            <div class="payment-feedback {{ $feedbackType === 'success' ? 'success' : ($feedbackType === 'error' ? 'error' : ($feedbackType === 'info' ? 'info' : '')) }}" data-payment-feedback data-initial-message="{{ $feedbackMessage ?? '' }}" data-initial-type="{{ $feedbackType ?? '' }}">{{ $feedbackMessage }}</div>
            <a href="{{ route('cart.index') }}" class="back-link">&larr; Kembali ke Keranjang</a>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'links' => $footerLinks,
    'copyright' => '© ' . date('Y') . ' Herbal Green'
])->render() !!}

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
                    setFeedback('Silakan pilih metode pembayaran terlebih dahulu.', 'error');
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
