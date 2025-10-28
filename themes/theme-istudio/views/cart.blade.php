<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/' . ($theme ?? 'theme-istudio') . '/css/style.css') }}" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .hero-strip {
            background: linear-gradient(135deg, rgba(15, 23, 43, 0.95), rgba(26, 32, 55, 0.92));
            color: #fff;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero-strip::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at right top, rgba(255, 180, 0, 0.35), transparent 55%);
            pointer-events: none;
        }
        .hero-strip h1 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .hero-strip p {
            max-width: 560px;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 0.75rem;
        }
        .breadcrumb-custom {
            display: inline-flex;
            gap: 0.75rem;
            padding: 0.65rem 1.2rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.85rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .letter-spacing-08 { letter-spacing: .08em; }
        .breadcrumb-custom a { color: rgba(255, 255, 255, 0.85); }
        .cart-section { margin-top: -60px; position: relative; z-index: 5; }
        .cart-card,
        .summary-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 35px 60px rgba(15, 23, 43, 0.12);
        }
        .cart-card { padding: 32px 36px; }
        .cart-card table { width: 100%; border-collapse: collapse; }
        .cart-card thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: .12em;
            color: #6c757d;
            padding-bottom: 14px;
        }
        .cart-card tbody tr td {
            padding: 22px 0;
            border-top: 1px solid rgba(15, 23, 43, 0.08);
            vertical-align: middle;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .cart-item img {
            width: 96px;
            height: 96px;
            object-fit: cover;
            border-radius: 18px;
        }
        .cart-item h6 {
            font-size: 1.05rem;
            margin-bottom: 6px;
            letter-spacing: .04em;
        }
        .cart-item a { color: #0f172b; text-decoration: none; }
        .promo-label {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 180, 0, 0.18);
            color: #c87600;
            font-size: 0.7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .price-stack {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            align-items: flex-start;
        }
        .price-stack.single-line {
            flex-direction: row;
            gap: 0.4rem;
        }
        .price-original { color: rgba(15, 23, 43, 0.4); text-decoration: line-through; font-size: 0.85rem; }
        .price-current { font-weight: 700; color: #0f172b; }
        .quantity-control {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 43, 0.1);
            overflow: hidden;
            background: #f8fafc;
        }
        .quantity-control button {
            background: transparent;
            border: none;
            width: 38px;
            height: 38px;
            font-weight: 600;
            color: #0f172b;
        }
        .quantity-control input {
            width: 64px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
        }
        .remove-button {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #d32f2f;
            line-height: 1;
        }
        .summary-card {
            padding: 36px;
            color: #fff;
            background: linear-gradient(145deg, #0f172b, #1f2937);
            position: sticky;
            top: 120px;
        }
        .summary-card h3 {
            font-size: 1.6rem;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .summary-list { list-style: none; padding: 0; margin: 28px 0 0; }
        .summary-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            font-size: 0.95rem;
        }
        .summary-list li.total { font-size: 1.35rem; font-weight: 700; letter-spacing: .06em; }
        .summary-note { color: rgba(255, 255, 255, 0.6); font-size: 0.85rem; margin-top: 22px; }
        .cart-feedback {
            display: none;
            margin-top: 18px;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .cart-feedback.visible { display: block; }
        .cart-feedback.error { background: #fff4f4; color: #c53030; }
        .cart-feedback.success { background: #e8f9f3; color: #0f766e; }
        .cart-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 22px; }
        .btn-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.9rem 1.5rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .btn-outline-dark { border: 1px solid rgba(15, 23, 43, 0.25); color: #0f172b; background: transparent; }
        .btn-outline-dark:hover { border-color: #0f172b; }
        .btn-primary-pill { background: #ffb400; color: #0f172b; border: none; }
        .btn-primary-pill.disabled,
        .btn-primary-pill[aria-disabled="true"] { opacity: 0.5; pointer-events: none; }
        .cart-empty {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 25px 60px rgba(15, 23, 43, 0.1);
            padding: 72px 48px;
            text-align: center;
        }
        .cart-empty h3 { font-size: 2rem; letter-spacing: .08em; text-transform: uppercase; }
        .cart-empty p { color: #6b7280; max-width: 460px; margin: 12px auto 0; }
        .cart-empty .btn-pill { margin-top: 28px; }
        @media (max-width: 991px) {
            .summary-card { position: static; }
            .hero-strip { padding-top: 100px; padding-bottom: 60px; }
            .cart-card { padding: 28px 24px; }
        }
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-istudio';
    $settings = PageSetting::forPage('cart');
    $cartSummary = $cartSummary ?? Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $shippingEnabled = $shippingEnabled ?? false;
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));

    $title = $settings['title'] ?? 'Keranjang Belanja';
    $subtitle = $settings['subtitle'] ?? 'Periksa kembali koleksi pilihan Anda sebelum melanjutkan ke checkout.';
    $emptyMessage = $settings['empty.message'] ?? 'Keranjang Anda masih kosong. Jelajahi katalog kami untuk menemukan inspirasi baru.';
    $emptyButton = $settings['empty.button'] ?? 'Mulai Belanja';
    $shippingLabel = $settings['button.shipping'] ?? 'Lanjut ke Pengiriman';
    $paymentLabel = $settings['button.payment'] ?? 'Lanjut ke Pembayaran';
    $primaryButton = $shippingEnabled ? $shippingLabel : $paymentLabel;
    $actionUrl = $shippingEnabled ? route('checkout.shipping') : route('checkout.payment');
    $items = $cartSummary['items'] ?? [];
    if (! is_array($items)) {
        $items = []; 
    }
    $itemCount = count($items);
    $hasItems = $itemCount > 0;
@endphp

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<section class="hero-strip">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1>{{ $title }}</h1>
                @if(!empty($subtitle))
                    <p>{{ $subtitle }}</p>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="breadcrumb-custom">
                    <a href="{{ url('/') }}">Home</a>
                    <span>/</span>
                    <span>Keranjang</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cart-section">
    <div class="container py-5">
        <div id="cart-content" class="row g-4 align-items-start @if(! $hasItems) d-none @endif" style="{{ $hasItems ? '' : 'display:none;' }}">
            <div class="col-lg-8">
                <div class="cart-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 text-uppercase letter-spacing-08 mb-0">Ringkasan Keranjang</h2>
                        <span class="text-muted small">{{ $itemCount }} produk</span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-start">Produk</th>
                                    <th class="text-start">Harga</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody data-cart-body>
                                @foreach($items as $item)
                                    @php
                                        $hasPromo = $item['has_promo'] ?? false;
                                        $priceStackClass = $hasPromo ? 'price-stack' : 'price-stack single-line';
                                        $subtotalStackClass = $hasPromo && ! empty($item['original_subtotal_formatted']) ? 'price-stack' : 'price-stack single-line';
                                    @endphp
                                    <tr data-product-id="{{ $item['product_id'] }}">
                                        <td>
                                            <div class="cart-item">
                                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
                                                <div>
                                                    <h6 class="mb-0"><a href="{{ $item['product_url'] }}">{{ $item['name'] }}</a></h6>
                                                    @if($hasPromo && !empty($item['promo_label']))
                                                        <span class="promo-label mt-2">{{ $item['promo_label'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td data-item-price>
                                            <div class="{{ $priceStackClass }}">
                                                @if($hasPromo && !empty($item['original_price_formatted']))
                                                    <span class="price-original">Rp {{ $item['original_price_formatted'] }}</span>
                                                @endif
                                                <span class="price-current">Rp {{ $item['price_formatted'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="quantity-control" data-quantity-control>
                                                <button type="button" data-action="decrease" aria-label="Kurangi jumlah">−</button>
                                                <input type="number" value="{{ $item['quantity'] }}" min="1" aria-label="Jumlah">
                                                <button type="button" data-action="increase" aria-label="Tambah jumlah">+</button>
                                            </div>
                                        </td>
                                        <td class="text-end" data-item-subtotal>
                                            <div class="{{ $subtotalStackClass }}">
                                                @if($hasPromo && !empty($item['original_subtotal_formatted']))
                                                    <span class="price-original">Rp {{ $item['original_subtotal_formatted'] }}</span>
                                                @endif
                                                <span class="price-current">Rp {{ $item['subtotal_formatted'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="remove-button" data-remove-item aria-label="Hapus produk">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="cart-feedback" data-cart-status></div>
                    <div class="cart-actions">
                        <a href="{{ url('/produk') }}" class="btn-pill btn-outline-dark">Lanjut Belanja</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="summary-card">
                    <h3>Ringkasan</h3>
                    <ul class="summary-list">
                        <li class="{{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-summary-original>
                            <span>Harga Normal</span>
                            <span>Rp {{ $cartSummary['original_total_formatted'] ?? $cartSummary['total_price_formatted'] }}</span>
                        </li>
                        <li class="text-success {{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-summary-discount>
                            <span>Potongan Promo</span>
                            <span>-Rp {{ $cartSummary['discount_total_formatted'] ?? '0' }}</span>
                        </li>
                        <li>
                            <span>Subtotal</span>
                            <span>Rp <span data-cart-subtotal>{{ $cartSummary['subtotal_price_formatted'] ?? $cartSummary['total_price_formatted'] }}</span></span>
                        </li>
                        <li class="total">
                            <span>Total</span>
                            <span>Rp <span data-cart-grand-total>{{ $cartSummary['total_price_formatted'] }}</span></span>
                        </li>
                    </ul>
                    <div class="cart-actions">
                        <a href="{{ $actionUrl }}" class="btn-pill btn-primary-pill {{ $hasItems ? '' : 'disabled' }}" data-cart-action aria-disabled="{{ $hasItems ? 'false' : 'true' }}">{{ $primaryButton }}</a>
                    </div>
                    <p class="summary-note">Harga sudah termasuk PPN. Biaya pengiriman akan dihitung pada tahap berikutnya.</p>
                </div>
            </div>
        </div>

        <div id="cart-empty" class="cart-empty @if($hasItems) d-none @endif" style="{{ $hasItems ? 'display:none;' : '' }}">
            <h3>{{ $title }}</h3>
            <p>{{ $emptyMessage }}</p>
            <a href="{{ url('/produk') }}" class="btn-pill btn-primary-pill">{{ $emptyButton }}</a>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
    'brand' => $navigation['brand'],
])->render() !!}

<script>
    (function(){
        const csrf = '{{ csrf_token() }}';
        const updateUrl = '{{ route('cart.items.update', ['product' => '__ID__']) }}';
        const destroyUrl = '{{ route('cart.items.destroy', ['product' => '__ID__']) }}';
        const paymentUrl = '{{ route('checkout.payment') }}';
        const shippingUrl = '{{ route('checkout.shipping') }}';
        const shippingEnabled = {{ $shippingEnabled ? 'true' : 'false' }};

        const cartBody = document.querySelector('[data-cart-body]');
        const cartContent = document.getElementById('cart-content');
        const emptyState = document.getElementById('cart-empty');
        const statusBox = document.querySelector('[data-cart-status]');
        const totalDisplay = document.querySelector('[data-cart-grand-total]');
        const subtotalDisplay = document.querySelector('[data-cart-subtotal]');
        const summaryOriginal = document.querySelector('[data-summary-original]');
        const summaryDiscount = document.querySelector('[data-summary-discount]');
        const actionButton = document.querySelector('[data-cart-action]');

        function renderPriceMarkup(item) {
            const hasPromo = !!item.has_promo;
            const stackClass = hasPromo ? 'price-stack' : 'price-stack single-line';
            const original = hasPromo ? (item.original_price_formatted || '') : '';
            const parts = [];
            if (hasPromo && original) {
                parts.push(`<span class="price-original">Rp ${original}</span>`);
            }
            parts.push(`<span class="price-current">Rp ${item.price_formatted}</span>`);
            return `<div class="${stackClass}">${parts.join('')}</div>`;
        }

        function renderSubtotalMarkup(item) {
            const hasPromo = !!item.has_promo;
            const stackClass = hasPromo && item.original_subtotal_formatted ? 'price-stack' : 'price-stack single-line';
            const parts = [];
            if (hasPromo && item.original_subtotal_formatted) {
                parts.push(`<span class="price-original">Rp ${item.original_subtotal_formatted}</span>`);
            }
            parts.push(`<span class="price-current">Rp ${item.subtotal_formatted}</span>`);
            return `<div class="${stackClass}">${parts.join('')}</div>`;
        }

        function buildRow(item) {
            const tr = document.createElement('tr');
            tr.dataset.productId = item.product_id;

            const productCell = document.createElement('td');
            const productWrapper = document.createElement('div');
            productWrapper.className = 'cart-item';
            const img = document.createElement('img');
            img.src = item.image_url;
            img.alt = item.name;
            const info = document.createElement('div');
            const title = document.createElement('h6');
            title.className = 'mb-0';
            const link = document.createElement('a');
            link.href = item.product_url;
            link.textContent = item.name;
            title.appendChild(link);
            info.appendChild(title);
            if (item.has_promo && item.promo_label) {
                const badge = document.createElement('span');
                badge.className = 'promo-label mt-2';
                badge.textContent = item.promo_label;
                info.appendChild(badge);
            }
            productWrapper.append(img, info);
            productCell.appendChild(productWrapper);

            const priceCell = document.createElement('td');
            priceCell.setAttribute('data-item-price', 'true');
            priceCell.innerHTML = renderPriceMarkup(item);

            const quantityCell = document.createElement('td');
            quantityCell.className = 'text-center';
            const control = document.createElement('div');
            control.className = 'quantity-control';
            control.setAttribute('data-quantity-control', 'true');
            control.innerHTML = '<button type="button" data-action="decrease" aria-label="Kurangi jumlah">−</button>' +
                `<input type="number" value="${item.quantity}" min="1" aria-label="Jumlah">` +
                '<button type="button" data-action="increase" aria-label="Tambah jumlah">+</button>';
            quantityCell.appendChild(control);

            const subtotalCell = document.createElement('td');
            subtotalCell.className = 'text-end';
            subtotalCell.setAttribute('data-item-subtotal', 'true');
            subtotalCell.innerHTML = renderSubtotalMarkup(item);

            const actionCell = document.createElement('td');
            actionCell.className = 'text-end';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-button';
            removeBtn.setAttribute('data-remove-item', 'true');
            removeBtn.setAttribute('aria-label', 'Hapus produk');
            removeBtn.innerHTML = '&times;';
            actionCell.appendChild(removeBtn);

            tr.append(productCell, priceCell, quantityCell, subtotalCell, actionCell);
            return tr;
        }

        function showStatus(message, type = 'success') {
            if (!statusBox) { return; }
            statusBox.textContent = message || '';
            statusBox.classList.remove('error', 'success', 'visible');
            if (!message) { return; }
            statusBox.classList.add(type === 'error' ? 'error' : 'success', 'visible');
            setTimeout(() => { statusBox?.classList.remove('visible'); }, 2400);
        }

        function setActionButtonState(isEnabled) {
            if (!actionButton) { return; }
            const targetUrl = shippingEnabled ? shippingUrl : paymentUrl;
            if (isEnabled) {
                actionButton.classList.remove('disabled');
                actionButton.setAttribute('aria-disabled', 'false');
                actionButton.setAttribute('href', targetUrl);
            } else {
                actionButton.classList.add('disabled');
                actionButton.setAttribute('aria-disabled', 'true');
                actionButton.removeAttribute('href');
            }
        }

        function toggleEmptyState(hasItems) {
            if (hasItems) {
                cartContent?.classList.remove('d-none');
                if (cartContent) cartContent.style.display = '';
                emptyState?.classList.add('d-none');
                if (emptyState) emptyState.style.display = 'none';
            } else {
                cartContent?.classList.add('d-none');
                if (cartContent) cartContent.style.display = 'none';
                emptyState?.classList.remove('d-none');
                if (emptyState) emptyState.style.display = '';
            }
        }

        function applySummary(summary) {
            if (!summary || !cartBody) { return; }
            const items = summary.items || [];
            cartBody.innerHTML = '';
            items.forEach(item => cartBody.appendChild(buildRow(item)));
            if (totalDisplay) {
                totalDisplay.textContent = summary.total_price_formatted || '0';
            }
            if (subtotalDisplay) {
                subtotalDisplay.textContent = summary.subtotal_price_formatted || summary.total_price_formatted || '0';
            }
            if (summaryOriginal) {
                if ((summary.discount_total || 0) > 0) {
                    summaryOriginal.classList.remove('d-none');
                    const valueEl = summaryOriginal.querySelector('span:last-child');
                    if (valueEl) {
                        valueEl.textContent = 'Rp ' + (summary.original_total_formatted || summary.total_price_formatted || '0');
                    }
                } else {
                    summaryOriginal.classList.add('d-none');
                }
            }
            if (summaryDiscount) {
                if ((summary.discount_total || 0) > 0) {
                    summaryDiscount.classList.remove('d-none');
                    const valueEl = summaryDiscount.querySelector('span:last-child');
                    if (valueEl) {
                        valueEl.textContent = '-Rp ' + (summary.discount_total_formatted || '0');
                    }
                } else {
                    summaryDiscount.classList.add('d-none');
                }
            }
            setActionButtonState(items.length > 0);
            toggleEmptyState(items.length > 0);
            window.dispatchEvent(new CustomEvent('cart:updated', { detail: summary }));
        }

        function handleResponse(response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        }

        function updateQuantity(productId, quantity) {
            fetch(updateUrl.replace('__ID__', productId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ quantity: quantity })
            })
                .then(handleResponse)
                .then(data => {
                    applySummary(data.summary);
                    showStatus('Jumlah produk diperbarui.');
                })
                .catch(() => showStatus('Gagal memperbarui keranjang.', 'error'));
        }

        function removeItem(productId) {
            fetch(destroyUrl.replace('__ID__', productId), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                }
            })
                .then(handleResponse)
                .then(data => {
                    applySummary(data.summary);
                    showStatus('Produk dihapus dari keranjang.');
                })
                .catch(() => showStatus('Gagal menghapus produk.', 'error'));
        }

        cartBody?.addEventListener('click', function (event) {
            const actionButton = event.target.closest('button[data-action]');
            if (actionButton) {
                const row = actionButton.closest('tr[data-product-id]');
                const input = row?.querySelector('input[type="number"]');
                if (!row || !input) { return; }
                let value = parseInt(input.value || '1', 10);
                if (actionButton.dataset.action === 'increase') { value += 1; }
                if (actionButton.dataset.action === 'decrease') { value = Math.max(1, value - 1); }
                input.value = value;
                updateQuantity(row.dataset.productId, value);
                return;
            }
            const removeBtn = event.target.closest('[data-remove-item]');
            if (removeBtn) {
                const row = removeBtn.closest('tr[data-product-id]');
                if (!row) { return; }
                removeItem(row.dataset.productId);
            }
        });

        cartBody?.addEventListener('change', function (event) {
            const input = event.target.closest('input[type="number"]');
            if (!input) { return; }
            const row = input.closest('tr[data-product-id]');
            if (!row) { return; }
            let value = parseInt(input.value || '1', 10);
            if (!Number.isFinite(value) || value < 1) { value = 1; }
            input.value = value;
            updateQuantity(row.dataset.productId, value);
        });
    })();
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ $assetBase('lib/wow/wow.min.js') }}"></script>
<script src="{{ $assetBase('lib/easing/easing.min.js') }}"></script>
<script src="{{ $assetBase('lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ $assetBase('lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ $assetBase('js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
