<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengiriman</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <style>
        body {
            background: #f5f5f5;
        }
        #shipping-page {
            padding: 3rem 2rem;
        }
        .shipping-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        .shipping-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }
        .shipping-card h2 {
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
            color: var(--color-primary);
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #35424a;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid #cfd8dc;
            transition: border-color 0.2s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--color-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
        }
        .shipping-methods {
            margin-top: 1.5rem;
        }
        .shipping-methods h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }
        .method-option {
            border: 1px solid #e0f2f1;
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .method-option.active {
            border-color: var(--color-primary);
            box-shadow: 0 8px 16px rgba(46,125,50,0.15);
        }
        .method-option input {
            display: none;
        }
        .method-info strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .method-info small {
            display: block;
            color: #607d8b;
        }
        .summary-card ul {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem 0;
        }
        .summary-card li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            color: #455a64;
        }
        .summary-card li.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #546e7a;
        }
        .cta-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.9rem 1rem;
            border: none;
            border-radius: 999px;
            background: var(--color-primary);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .cta-primary:disabled {
            background: #b0bec5;
            cursor: not-allowed;
        }
        .feedback {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            display: none;
        }
        .feedback.visible { display: block; }
        .feedback.error { background: #ffebee; color: #c62828; }
        .feedback.success { background: #e8f5e9; color: #2e7d32; }
        .product-item { display:flex; justify-content: space-between; align-items:flex-start; }
        .product-item .price-original { display:block; color:#9e9e9e; text-decoration:line-through; font-size:0.85rem; }
        .product-item .price-current { display:block; color:#2e7d32; font-weight:600; }
        .product-item .promo-label { display:inline-flex; align-items:center; background:#e53935; color:#fff; border-radius:999px; padding:3px 10px; font-size:0.7rem; text-transform:uppercase; letter-spacing:.05em; margin-top:4px; }
        @media (max-width: 992px) {
            .shipping-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@php
    use App\Support\LayoutSettings;

    $navigation = LayoutSettings::navigation($theme);
    $footerConfig = LayoutSettings::footer($theme);
    $contact = $shippingData['contact'] ?? [];
    $addressData = $shippingData['address'] ?? [];
    $selectedRate = $shippingData['selected_rate'] ?? null;
    $couriers = $shippingConfig['couriers'] ?? [];
@endphp

{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<section id="shipping-page">
    <div class="shipping-grid">
        <div class="shipping-card">
            <h2>Informasi Pengiriman</h2>
            <form data-shipping-form>
                <div class="form-group">
                    <label for="recipient_name">Nama Penerima</label>
                    <input type="text" id="recipient_name" name="recipient_name" value="{{ $contact['name'] ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ $contact['email'] ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label for="phone">No. Telepon</label>
                    <input type="tel" id="phone" name="phone" value="{{ $contact['phone'] ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" rows="3" required>{{ $addressData['street'] ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label for="province">Provinsi</label>
                    <select id="province" name="province_code" data-selected="{{ $addressData['province_code'] ?? '' }}" required>
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->code }}" {{ ($addressData['province_code'] ?? '') === $province->code ? 'selected' : '' }}>{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="regency">Kota / Kabupaten</label>
                    <select id="regency" name="regency_code" data-selected="{{ $addressData['regency_code'] ?? '' }}" required>
                        <option value="">Pilih Kota/Kabupaten</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="district">Kecamatan</label>
                    <select id="district" name="district_code" data-selected="{{ $addressData['district_code'] ?? '' }}" required>
                        <option value="">Pilih Kecamatan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="village">Kelurahan</label>
                    <select id="village" name="village_code" data-selected="{{ $addressData['village_code'] ?? '' }}" required>
                        <option value="">Pilih Kelurahan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ $addressData['postal_code'] ?? '' }}" required>
                </div>

                <div class="shipping-methods" data-shipping-methods>
                    <h3>Metode Pengiriman</h3>
                    <div data-method-list class="method-list"></div>
                    <button type="button" class="cta-primary" data-fetch-rates>Cek Ongkir</button>
                    <div class="feedback error mt-2" data-rate-feedback></div>
                </div>

                <div class="feedback" data-form-feedback></div>
                <button type="submit" class="cta-primary mt-3" data-submit disabled>Lanjut ke Pembayaran</button>
            </form>
        </div>
        <div class="shipping-card summary-card">
            <h2>Ringkasan Pesanan</h2>
            <div class="product-list">
                @foreach($cartSummary['items'] as $item)
                    @php $hasPromo = $item['has_promo'] ?? false; @endphp
                    <div class="product-item">
                        <div>
                            <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                            @if($hasPromo && !empty($item['promo_label']))
                                <span class="promo-label">{{ $item['promo_label'] }}</span>
                            @endif
                        </div>
                        <div style="text-align:right;">
                            @if($hasPromo && !empty($item['original_subtotal_formatted']))
                                <span class="price-original">Rp {{ $item['original_subtotal_formatted'] }}</span>
                            @endif
                            <span class="price-current">Rp {{ $item['subtotal_formatted'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <ul>
                <li>
                    <span>Subtotal</span>
                    <span>Rp {{ $checkoutTotals['subtotal_formatted'] ?? $cartSummary['total_price_formatted'] }}</span>
                </li>
                <li>
                    <span>Ongkir</span>
                    <span data-summary-shipping>Rp {{ $checkoutTotals['shipping_cost_formatted'] ?? '0' }}</span>
                </li>
                <li class="total">
                    <span>Total</span>
                    <span data-summary-total>Rp {{ $checkoutTotals['grand_total_formatted'] ?? $cartSummary['total_price_formatted'] }}</span>
                </li>
            </ul>
            <p class="text-muted" style="font-size: 0.9rem;">Biaya ongkir akan dihitung berdasarkan kurir dan lokasi yang dipilih.</p>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script>
    (function(){
        const routes = {
            regencies: @json(route('shipping.locations.regencies')),
            districts: @json(route('shipping.locations.districts')),
            villages: @json(route('shipping.locations.villages')),
            quote: @json(route('checkout.shipping.quote')),
            store: @json(route('checkout.shipping.store')),
        };
        const provincesSelect = document.getElementById('province');
        const regencySelect = document.getElementById('regency');
        const districtSelect = document.getElementById('district');
        const villageSelect = document.getElementById('village');
        const postalInput = document.getElementById('postal_code');
        const methodsContainer = document.querySelector('[data-method-list]');
        const methodWrapper = document.querySelector('[data-shipping-methods]');
        const rateFeedback = document.querySelector('[data-rate-feedback]');
        const submitButton = document.querySelector('[data-submit]');
        const fetchButton = document.querySelector('[data-fetch-rates]');
        const form = document.querySelector('[data-shipping-form]');
        const summaryShipping = document.querySelector('[data-summary-shipping]');
        const summaryTotal = document.querySelector('[data-summary-total]');
        const formFeedback = document.querySelector('[data-form-feedback]');
        const csrf = '{{ csrf_token() }}';
        const couriers = @json($couriers);
        const initialSelection = @json([
            'regency' => $addressData['regency_code'] ?? null,
            'district' => $addressData['district_code'] ?? null,
            'village' => $addressData['village_code'] ?? null,
            'postal_code' => $addressData['postal_code'] ?? null,
            'rate' => $selectedRate,
        ]);
        let selectedRate = initialSelection.rate || null;

        function setSubmitState() {
            if (submitButton) {
                submitButton.disabled = !selectedRate;
            }
        }

        function updateTotals(cost) {
            if (summaryShipping) {
                summaryShipping.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(cost);
            }
            const subtotal = {{ (int) $cartSummary['total_price'] }};
            const total = subtotal + parseInt(cost || 0, 10);
            if (summaryTotal) {
                summaryTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            }
        }

        function showRateFeedback(message, isError = false) {
            if (!rateFeedback) return;
            rateFeedback.textContent = message;
            rateFeedback.classList.toggle('error', isError);
            rateFeedback.classList.toggle('visible', !!message);
        }

        function showFormFeedback(message, type = 'error') {
            if (!formFeedback) return;
            formFeedback.textContent = message;
            formFeedback.classList.remove('error', 'success');
            if (message) {
                formFeedback.classList.add('visible');
                formFeedback.classList.add(type === 'success' ? 'success' : 'error');
            } else {
                formFeedback.classList.remove('visible');
            }
        }

        function clearSelect(select, placeholder) {
            if (!select) return;
            select.innerHTML = '';
            const option = document.createElement('option');
            option.value = '';
            option.textContent = placeholder;
            select.appendChild(option);
        }

        function populateSelect(select, items, selectedValue) {
            if (!select) return;
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.code;
                option.textContent = item.name;
                if (String(selectedValue || '') === String(item.code)) {
                    option.selected = true;
                }
                if (typeof item.postal_code !== 'undefined') {
                    option.dataset.postal = item.postal_code;
                }
                select.appendChild(option);
            });
            select.dispatchEvent(new Event('change'));
        }

        function fetchLocations(url, params, select, placeholder, selectedValue) {
            if (!select) return Promise.resolve();
            clearSelect(select, placeholder);
            if (!params) return Promise.resolve();
            const query = new URLSearchParams(params).toString();
            return fetch(url + '?' + query)
                .then(response => response.json())
                .then(data => {
                    if ((data.status || '') !== 'ok') {
                        return [];
                    }
                    populateSelect(select, data.data || [], selectedValue);
                    return data.data || [];
                })
                .catch(() => []);
        }

        function renderMethods(rates) {
            if (!methodsContainer) return;
            methodsContainer.innerHTML = '';
            if (!rates || rates.length === 0) {
                methodsContainer.innerHTML = '<p class="text-muted">Tidak ada metode pengiriman untuk lokasi ini.</p>';
                selectedRate = null;
                updateTotals(0);
                setSubmitState();
                return;
            }

            let preselectedKey = null;
            if (selectedRate) {
                preselectedKey = selectedRate.courier + '|' + selectedRate.service;
            } else if (rates.length > 0) {
                selectedRate = rates[0];
                preselectedKey = selectedRate.courier + '|' + selectedRate.service;
            }

            rates.forEach(rate => {
                const key = rate.courier + '|' + rate.service;
                const option = document.createElement('label');
                option.className = 'method-option';
                option.dataset.rateKey = key;
                option.innerHTML = `
                    <input type="radio" name="shipping_rate" value="${key}">
                    <div class="method-info">
                        <strong>${rate.courier_name || rate.courier} - ${rate.service}</strong>
                        <small>${rate.description || ''}</small>
                    </div>
                    <div class="method-price">
                        <strong>Rp ${new Intl.NumberFormat('id-ID').format(rate.cost)}</strong>
                        <small>${rate.etd ? rate.etd + ' hari' : ''}</small>
                    </div>
                `;
                if (preselectedKey && preselectedKey === key) {
                    option.classList.add('active');
                    option.querySelector('input').checked = true;
                    updateTotals(rate.cost);
                }
                option.addEventListener('click', () => {
                    methodsContainer.querySelectorAll('.method-option').forEach(el => el.classList.remove('active'));
                    option.classList.add('active');
                    option.querySelector('input').checked = true;
                    selectedRate = rate;
                    updateTotals(rate.cost);
                    setSubmitState();
                });
                methodsContainer.appendChild(option);
            });

            setSubmitState();
        }

        function collectAddress() {
            return {
                province_code: provincesSelect?.value || '',
                regency_code: regencySelect?.value || '',
                district_code: districtSelect?.value || '',
                village_code: villageSelect?.value || '',
                postal_code: postalInput?.value || '',
            };
        }

        function fetchRates() {
            const address = collectAddress();
            if (!address.province_code || !address.regency_code || !address.district_code || !address.village_code || !address.postal_code) {
                showRateFeedback('Lengkapi lokasi pengiriman terlebih dahulu.', true);
                return;
            }
            showRateFeedback('Mengambil daftar ongkir...', false);
            const payload = Object.assign({}, address, { couriers: couriers });
            fetch(routes.quote, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if ((data.status || '') !== 'ok') {
                    throw new Error(data.message || 'Gagal mengambil ongkir');
                }
                const rates = data.data.rates || [];
                renderMethods(rates);
                if (!selectedRate && rates.length > 0) {
                    selectedRate = rates[0];
                    updateTotals(selectedRate.cost);
                    setSubmitState();
                }
                showRateFeedback('', false);
            })
            .catch(error => {
                renderMethods([]);
                showRateFeedback(error.message || 'Tidak dapat memuat ongkir.', true);
            });
        }

        if (fetchButton) {
            fetchButton.addEventListener('click', fetchRates);
        }

        if (provincesSelect) {
            provincesSelect.addEventListener('change', () => {
                const province = provincesSelect.value;
                clearSelect(regencySelect, 'Pilih Kota/Kabupaten');
                clearSelect(districtSelect, 'Pilih Kecamatan');
                clearSelect(villageSelect, 'Pilih Kelurahan');
                if (!province) return;
                fetchLocations(routes.regencies, { province: province }, regencySelect, 'Pilih Kota/Kabupaten', null);
            });
        }

        if (regencySelect) {
            regencySelect.addEventListener('change', () => {
                const regency = regencySelect.value;
                clearSelect(districtSelect, 'Pilih Kecamatan');
                clearSelect(villageSelect, 'Pilih Kelurahan');
                if (!regency) return;
                fetchLocations(routes.districts, { regency: regency }, districtSelect, 'Pilih Kecamatan', null);
            });
        }

        if (districtSelect) {
            districtSelect.addEventListener('change', () => {
                const district = districtSelect.value;
                clearSelect(villageSelect, 'Pilih Kelurahan');
                if (!district) return;
                fetchLocations(routes.villages, { district: district }, villageSelect, 'Pilih Kelurahan', null);
            });
        }

        if (villageSelect) {
            villageSelect.addEventListener('change', () => {
                const selected = villageSelect.selectedOptions[0];
                if (selected && selected.dataset.postal && !postalInput.value) {
                    postalInput.value = selected.dataset.postal;
                }
            });
        }

        if (form) {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (!selectedRate) {
                    showFormFeedback('Silakan pilih metode pengiriman.', 'error');
                    return;
                }
                showFormFeedback('Menyimpan informasi pengiriman...', 'success');
                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());
                payload.selected = selectedRate;
                fetch(routes.store, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if ((data.status || '') !== 'ok') {
                        throw new Error(data.message || 'Gagal menyimpan pengiriman');
                    }
                    window.location.href = data.redirect || '{{ route('checkout.payment') }}';
                })
                .catch(error => {
                    showFormFeedback(error.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                });
            });
        }

        function initializeSelections() {
            if (!provincesSelect) return;
            const provinceValue = provincesSelect.value;
            if (provinceValue) {
                fetchLocations(routes.regencies, { province: provinceValue }, regencySelect, 'Pilih Kota/Kabupaten', initialSelection.regency)
                    .then(() => {
                        if (initialSelection.regency) {
                            return fetchLocations(routes.districts, { regency: initialSelection.regency }, districtSelect, 'Pilih Kecamatan', initialSelection.district);
                        }
                    })
                    .then(() => {
                        if (initialSelection.district) {
                            return fetchLocations(routes.villages, { district: initialSelection.district }, villageSelect, 'Pilih Kelurahan', initialSelection.village);
                        }
                    })
                    .then(() => {
                        if (initialSelection.postal_code && postalInput) {
                            postalInput.value = initialSelection.postal_code;
                        }
                        if (initialSelection.rate) {
                            fetchRates();
                        }
                    });
            }
        }

        initializeSelections();
        setSubmitState();
        updateTotals(selectedRate ? selectedRate.cost : ({{ $checkoutTotals['shipping_cost'] ?? 0 }}));
    })();
</script>

{!! view()->file(base_path('themes/' . $theme . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $theme,
])->render() !!}
</body>
</html>
