<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengiriman</title>
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
        body { background: #f8f9fb; }
        .shipping-wrapper { padding: 100px 0; }
        .shipping-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(15, 23, 43, 0.08);
            padding: 40px;
        }
        .shipping-card h2 {
            font-size: 32px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #0f172b;
            margin-bottom: 28px;
        }
        .shipping-card .form-label {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: .12em;
            color: #6c757d;
        }
        .shipping-card .form-control,
        .shipping-card .form-select,
        .shipping-card textarea {
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 43, 0.08);
            padding: 14px 16px;
            background: #f8f9fb;
            font-size: 0.95rem;
        }
        .shipping-card .form-control:focus,
        .shipping-card .form-select:focus,
        .shipping-card textarea:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(255, 180, 0, 0.15);
        }
        .method-option {
            border: 1px solid rgba(15, 23, 43, 0.08);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .method-option.active {
            border-color: var(--bs-primary);
            box-shadow: 0 15px 30px rgba(255, 180, 0, 0.18);
        }
        .method-option input { display: none; }
        .summary-card {
            background: linear-gradient(145deg, #0f172b, #1f2937);
            color: #fff;
            border-radius: 18px;
            padding: 42px;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .summary-card::after {
            content: "";
            position: absolute;
            inset: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            pointer-events: none;
        }
        .summary-card h2 {
            font-size: 30px;
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 28px;
        }
        .summary-card ul { list-style: none; padding: 0; margin: 28px 0 0; position: relative; z-index: 1; }
        .summary-card li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
        }
        .summary-card li.total {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--bs-primary);
            margin-top: 16px;
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 14px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.85);
        }
        .product-item .promo-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            border-radius: 999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-top: 0.35rem;
        }
        .price-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.15rem;
        }
        .price-stack .price-original {
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }
        .price-stack .price-current {
            font-weight: 700;
        }
        .btn-primary,
        .btn-outline-primary {
            border-radius: 999px;
            padding: 0.85rem 1.25rem;
            font-weight: 600;
            letter-spacing: .05em;
        }
        .btn-outline-primary { border-width: 2px; }
        .feedback {
            display: none;
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 0.9rem;
        }
        .feedback.visible { display: block; }
        .feedback.error { background: #fff4f4; color: #c53030; }
        .feedback.success { background: #e8f9f3; color: #0f766e; }
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-istudio';
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = $cartSummary ?? Cart::summary();
    $contact = $shippingData['contact'] ?? [];
    $addressData = $shippingData['address'] ?? [];
    $selectedRate = $shippingData['selected_rate'] ?? null;
    $couriers = $shippingConfig['couriers'] ?? [];
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $settings = PageSetting::forPage('shipping');
    $formHeading = $settings['form.heading'] ?? 'Detail Pengiriman';
    $methodsHeading = $settings['methods.heading'] ?? 'Pilih Metode Pengiriman';
    $fetchLabel = $settings['methods.fetch_label'] ?? 'Cek Ongkir';
    $buttonLabel = $settings['form.button_label'] ?? 'Lanjut ke Pembayaran';
    $summaryHeading = $settings['summary.heading'] ?? 'Ringkasan';
    $summaryNote = $settings['summary.note'] ?? 'Biaya ongkir dihitung otomatis sesuai kurir dan tujuan Anda.';
@endphp

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<section class="container-fluid shipping-wrapper">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="shipping-card">
                    <h2>{{ $formHeading }}</h2>
                    <form data-shipping-form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" class="form-control" name="recipient_name" value="{{ $contact['name'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ $contact['email'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon</label>
                                <input type="tel" class="form-control" name="phone" value="{{ $contact['phone'] ?? '' }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" name="address" rows="3" required>{{ $addressData['street'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select class="form-select" id="province" name="province_code" data-selected="{{ $addressData['province_code'] ?? '' }}" required>
                                    <option value="">Pilih Provinsi</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->code }}" {{ ($addressData['province_code'] ?? '') === $province->code ? 'selected' : '' }}>{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota / Kabupaten</label>
                                <select class="form-select" id="regency" name="regency_code" data-selected="{{ $addressData['regency_code'] ?? '' }}" required>
                                    <option value="">Pilih Kota/Kabupaten</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kecamatan</label>
                                <select class="form-select" id="district" name="district_code" data-selected="{{ $addressData['district_code'] ?? '' }}" required>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kelurahan</label>
                                <select class="form-select" id="village" name="village_code" data-selected="{{ $addressData['village_code'] ?? '' }}" required>
                                    <option value="">Pilih Kelurahan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ $addressData['postal_code'] ?? '' }}" required>
                            </div>
                        </div>

                        <div class="mt-4" data-shipping-methods>
                            <h5 class="fw-bold text-uppercase mb-3">{{ $methodsHeading }}</h5>
                            <div data-method-list></div>
                            <button type="button" class="btn btn-outline-primary w-100 mt-2" data-fetch-rates>{{ $fetchLabel }}</button>
                            <div class="feedback error" data-rate-feedback></div>
                        </div>

                        <div class="feedback" data-form-feedback></div>
                        <button type="submit" class="btn btn-primary w-100 mt-3" data-submit disabled>{{ $buttonLabel }}</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="summary-card">
                    <h2>{{ $summaryHeading }}</h2>
                    <div class="mt-3 position-relative z-1">
                        @foreach($cartSummary['items'] as $item)
                            <div class="product-item">
                                <div>
                                    <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                                    @if(!empty($item['has_promo']) && !empty($item['promo_label']))
                                        <span class="promo-label">{{ $item['promo_label'] }}</span>
                                    @endif
                                    @if(!empty($item['promo_audience_label']))
                                        <span class="promo-label">{{ $item['promo_audience_label'] }}</span>
                                    @endif
                                </div>
                                <div class="price-stack">
                                    @if(!empty($item['has_promo']))
                                        <span class="price-original">Rp {{ $item['original_subtotal_formatted'] }}</span>
                                    @endif
                                    <span class="price-current">Rp {{ $item['subtotal_formatted'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <ul>
                        <li class="{{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-summary-original>
                            <span>Harga Normal</span>
                            <span>Rp {{ $cartSummary['original_total_formatted'] }}</span>
                        </li>
                        <li class="text-success {{ ($cartSummary['discount_total'] ?? 0) > 0 ? '' : 'd-none' }}" data-summary-discount>
                            <span>Diskon Promo</span>
                            <span>-Rp {{ $cartSummary['discount_total_formatted'] }}</span>
                        </li>
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
                    @if(!empty($summaryNote))
                    <p class="mt-4 mb-0 text-white-50 position-relative z-1">{{ $summaryNote }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
    'brand' => $navigation['brand'],
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
        const rateFeedback = document.querySelector('[data-rate-feedback]');
        const submitButton = document.querySelector('[data-submit]');
        const fetchButton = document.querySelector('[data-fetch-rates]');
        const form = document.querySelector('[data-shipping-form]');
        const summaryShipping = document.querySelector('[data-summary-shipping]');
        const summaryTotal = document.querySelector('[data-summary-total]');
        const formFeedback = document.querySelector('[data-form-feedback]');
        const csrf = '{{ csrf_token() }}';
        const couriers = @json($couriers);
        const initialSelection = {{ Illuminate\Support\Js::from([
            'regency'     => $addressData['regency_code'] ?? null,
            'district'    => $addressData['district_code'] ?? null,
            'village'     => $addressData['village_code'] ?? null,
            'postal_code' => $addressData['postal_code'] ?? null,
            'rate'        => $selectedRate,
            'shipping_cost' => $checkoutTotals['shipping_cost'] ?? 0,
        ]) }};
        let selectedRate = initialSelection.rate || null;
        let latestRates = [];

        function getRateKey(rate) {
            if (!rate) return null;
            if (typeof rate === 'string') return rate;
            if (rate.rate_key) return rate.rate_key;
            if (rate.key) return rate.key;
            if (rate.id) return rate.id;
            const courier = rate.courier || rate.code || '';
            const service = rate.service || rate.name || '';
            return [courier, service].filter(Boolean).join('|');
        }

        function getRateCost(rate) {
            if (!rate) return 0;
            return parseInt(rate.cost ?? rate.price ?? rate.value ?? 0, 10);
        }

        function setSubmitState() {
            if (submitButton) {
                submitButton.disabled = !selectedRate;
            }
        }

        function updateTotals(cost) {
            const baseSubtotal = {{ (int) ($checkoutTotals['subtotal'] ?? $cartSummary['total_price']) }};
            const shippingCost = parseInt(cost ?? 0, 10);
            if (summaryShipping) {
                summaryShipping.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(shippingCost);
            }
            if (summaryTotal) {
                summaryTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(baseSubtotal + shippingCost);
            }
        }

        function showRateFeedback(message, isError = false) {
            if (!rateFeedback) return;
            rateFeedback.textContent = message;
            rateFeedback.classList.remove('visible', 'error');
            if (message) {
                rateFeedback.classList.add('visible');
                if (isError) rateFeedback.classList.add('error');
            }
        }

        function showFormFeedback(message, type = 'error') {
            if (!formFeedback) return;
            formFeedback.textContent = message;
            formFeedback.classList.remove('visible', 'error', 'success');
            if (message) {
                formFeedback.classList.add('visible');
                formFeedback.classList.add(type === 'success' ? 'success' : 'error');
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

        function autoSelectPostal(select) {
            if (!select) return;
            const selected = select.options[select.selectedIndex];
            if (selected && selected.dataset.postal && postalInput && !postalInput.value) {
                postalInput.value = selected.dataset.postal;
            }
        }

        function renderMethods(rates) {
            if (!methodsContainer) return;
            latestRates = Array.isArray(rates) ? rates.filter(rate => rate && typeof rate === 'object') : [];
            methodsContainer.innerHTML = '';
            if (latestRates.length === 0) {
                methodsContainer.innerHTML = '<p class="text-muted mb-0">Belum ada opsi pengiriman tersedia. Lengkapi alamat lalu klik cek ongkir.</p>';
                selectedRate = null;
                updateTotals(initialSelection.shipping_cost || 0);
                setSubmitState();
                return;
            }

            const selectedKey = getRateKey(selectedRate);
            let activeRate = null;

            latestRates.forEach((rate, index) => {
                const key = getRateKey(rate);
                const cost = getRateCost(rate);
                const option = document.createElement('label');
                option.className = 'method-option';
                option.innerHTML = `
                    <input type="radio" name="shipping_rate" value="${key}">
                    <div>
                        <strong>${(rate.courier_name || rate.courier || rate.code || 'Kurir').toUpperCase()}${rate.service ? ' - ' + rate.service : ''}</strong>
                        <div class="text-muted small">${[rate.description || '', rate.etd ? rate.etd + ' hari' : ''].filter(Boolean).join(' • ')}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold text-primary">Rp ${new Intl.NumberFormat('id-ID').format(cost)}</div>
                    </div>
                `;
                if (selectedKey && selectedKey === key) {
                    option.classList.add('active');
                    option.querySelector('input').checked = true;
                    activeRate = Object.assign({}, rate, { key: key });
                    updateTotals(cost);
                }
                option.addEventListener('click', () => {
                    methodsContainer.querySelectorAll('.method-option').forEach(el => el.classList.remove('active'));
                    option.classList.add('active');
                    option.querySelector('input').checked = true;
                    activeRate = Object.assign({}, rate, { key: key });
                    selectedRate = activeRate;
                    updateTotals(cost);
                    setSubmitState();
                });
                methodsContainer.appendChild(option);
                if (!selectedKey && index === 0) {
                    option.classList.add('active');
                    option.querySelector('input').checked = true;
                    activeRate = Object.assign({}, rate, { key: key });
                    updateTotals(cost);
                }
            });

            if (!activeRate && latestRates.length > 0) {
                const fallbackRate = latestRates[0];
                const fallbackKey = getRateKey(fallbackRate);
                const firstOption = methodsContainer.querySelector('.method-option');
                if (firstOption) {
                    firstOption.classList.add('active');
                    const input = firstOption.querySelector('input');
                    if (input) {
                        input.checked = true;
                    }
                }
                activeRate = Object.assign({}, fallbackRate, { key: fallbackKey });
                updateTotals(getRateCost(fallbackRate));
            }
            selectedRate = activeRate;
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

        function ensureAddressComplete(address) {
            return address.province_code && address.regency_code && address.district_code && address.village_code && address.postal_code;
        }

        function fetchRates() {
            const address = collectAddress();
            if (!ensureAddressComplete(address)) {
                showRateFeedback('Lengkapi alamat pengiriman terlebih dahulu.', true);
                return;
            }
            showRateFeedback('Mengambil ongkir...');
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
                showRateFeedback('');
                renderMethods(data.data?.rates || data.data || []);
            })
            .catch(error => {
                renderMethods([]);
                showRateFeedback(error.message || 'Tidak dapat memuat ongkir.', true);
            });
        }

        provincesSelect?.addEventListener('change', () => {
            const province = provincesSelect.value;
            clearSelect(regencySelect, 'Pilih Kota/Kabupaten');
            clearSelect(districtSelect, 'Pilih Kecamatan');
            clearSelect(villageSelect, 'Pilih Kelurahan');
            if (!province) return;
            fetchLocations(routes.regencies, { province: province }, regencySelect, 'Pilih Kota/Kabupaten', initialSelection.regency)
                .then(() => {
                    initialSelection.regency = null;
                });
        });

        regencySelect?.addEventListener('change', () => {
            const regency = regencySelect.value;
            clearSelect(districtSelect, 'Pilih Kecamatan');
            clearSelect(villageSelect, 'Pilih Kelurahan');
            if (!regency) return;
            fetchLocations(routes.districts, { regency: regency }, districtSelect, 'Pilih Kecamatan', initialSelection.district)
                .then(() => {
                    initialSelection.district = null;
                });
        });

        districtSelect?.addEventListener('change', () => {
            const district = districtSelect.value;
            clearSelect(villageSelect, 'Pilih Kelurahan');
            if (!district) return;
            fetchLocations(routes.villages, { district: district }, villageSelect, 'Pilih Kelurahan', initialSelection.village)
                .then(() => {
                    initialSelection.village = null;
                    autoSelectPostal(villageSelect);
                });
        });

        villageSelect?.addEventListener('change', () => {
            autoSelectPostal(villageSelect);
        });

        fetchButton?.addEventListener('click', () => {
            selectedRate = null;
            setSubmitState();
            fetchRates();
        });

        form?.addEventListener('submit', event => {
            event.preventDefault();
            if (!selectedRate) {
                showFormFeedback('Pilih metode pengiriman terlebih dahulu.');
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
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showFormFeedback('Pengiriman disimpan.', 'success');
                }
            })
            .catch(error => {
                showFormFeedback(error.message || 'Terjadi kesalahan saat menyimpan.', 'error');
            });
        });

        function initializeSelections() {
            if (!provincesSelect || !provincesSelect.value) {
                updateTotals(initialSelection.shipping_cost || 0);
                setSubmitState();
                return;
            }
            fetchLocations(routes.regencies, { province: provincesSelect.value }, regencySelect, 'Pilih Kota/Kabupaten', initialSelection.regency)
                .then(() => fetchLocations(routes.districts, { regency: regencySelect.value }, districtSelect, 'Pilih Kecamatan', initialSelection.district))
                .then(() => fetchLocations(routes.villages, { district: districtSelect.value }, villageSelect, 'Pilih Kelurahan', initialSelection.village))
                .then(() => {
                    autoSelectPostal(villageSelect);
                    if (initialSelection.rate && typeof initialSelection.rate === 'object') {
                        renderMethods([initialSelection.rate]);
                    } else {
                        updateTotals(initialSelection.shipping_cost || 0);
                        setSubmitState();
                    }
                });
        }

        initializeSelections();
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
