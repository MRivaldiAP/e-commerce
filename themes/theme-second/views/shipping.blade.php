<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengiriman</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        body { background: #f8f8f8; }
        .shipping-section { padding: 80px 0; }
        .shipping-card {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        .shipping-card h2 {
            font-size: 30px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1c1c1c;
            margin-bottom: 24px;
        }
        .shipping-card .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .shipping-card .form-label {
            font-weight: 600;
            color: #1c1c1c;
            margin-bottom: 0;
            letter-spacing: 0.2px;
        }
        .shipping-card .select-wrapper {
            position: relative;
        }
        .shipping-card .select-wrapper::after {
            content: "\f078";
            font-family: 'FontAwesome';
            position: absolute;
            inset-block: 0;
            right: 16px;
            display: flex;
            align-items: center;
            pointer-events: none;
            color: #7fad39;
            font-size: 14px;
        }
        .shipping-card .form-control,
        .shipping-card .form-select,
        .shipping-card textarea {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 14px 16px;
            font-size: 15px;
            background-color: #fafafa;
        }
        .shipping-card .form-select {
            padding-right: 42px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
        }
        .shipping-card .form-select option {
            color: #1c1c1c;
            font-weight: 500;
            white-space: nowrap;
        }
        .shipping-card .form-control:focus,
        .shipping-card .form-select:focus,
        .shipping-card textarea:focus {
            border-color: #7fad39;
            box-shadow: 0 0 0 0.25rem rgba(127, 173, 57, 0.2);
        }
        .shipping-method { border: 1px solid #f1f1f1; border-radius: 12px; padding: 18px; margin-bottom: 10px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; transition: border-color .2s ease, box-shadow .2s ease; }
        .shipping-method.active { border-color: #7fad39; box-shadow: 0 12px 25px rgba(127,173,57,0.18); }
        .shipping-method input { display: none; }
        .shipping-summary { background: #1c1c1c; color: #fff; border-radius: 12px; padding: 40px; height: 100%; }
        .shipping-summary h3 { font-size: 26px; font-weight: 700; text-transform: uppercase; margin-bottom: 25px; }
        .summary-item { display:flex; justify-content: space-between; font-size: 15px; color: rgba(255,255,255,0.8); margin-bottom: 10px; }
        .summary-total { display:flex; justify-content: space-between; font-size: 22px; font-weight: 700; margin-top: 20px; color: #7fad39; }
        .btn-ogani { background: #7fad39; color: #fff; border-radius: 30px; padding: 14px 20px; border:none; font-weight: 600; text-transform: uppercase; width: 100%; }
        .btn-ogani:disabled { background: #c5d7a5; cursor: not-allowed; }
        .feedback { display:none; margin-top: 12px; padding: 12px 14px; border-radius: 10px; font-size: 14px; }
        .feedback.visible { display:block; }
        .feedback.error { background: #fff2f0; color: #d94841; }
        .feedback.success { background: #effaf1; color: #2a7f3e; }
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

<section class="shipping-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="shipping-card">
                    <h2>Detail Pengiriman</h2>
                    <form data-shipping-form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Nama Penerima</label>
                                    <input type="text" class="form-control" name="recipient_name" value="{{ $contact['name'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="{{ $contact['email'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="tel" class="form-control" name="phone" value="{{ $contact['phone'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-field">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" name="address" rows="3" required>{{ $addressData['street'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Provinsi</label>
                                    <div class="select-wrapper">
                                        <select class="form-select" id="province" name="province_code" data-selected="{{ $addressData['province_code'] ?? '' }}" required>
                                            <option value="">Pilih Provinsi</option>
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->code }}" {{ ($addressData['province_code'] ?? '') === $province->code ? 'selected' : '' }}>{{ $province->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Kota / Kabupaten</label>
                                    <div class="select-wrapper">
                                        <select class="form-select" id="regency" name="regency_code" data-selected="{{ $addressData['regency_code'] ?? '' }}" required>
                                            <option value="">Pilih Kota/Kabupaten</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Kecamatan</label>
                                    <div class="select-wrapper">
                                        <select class="form-select" id="district" name="district_code" data-selected="{{ $addressData['district_code'] ?? '' }}" required>
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Kelurahan</label>
                                    <div class="select-wrapper">
                                        <select class="form-select" id="village" name="village_code" data-selected="{{ $addressData['village_code'] ?? '' }}" required>
                                            <option value="">Pilih Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label class="form-label">Kode Pos</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ $addressData['postal_code'] ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4" data-shipping-methods>
                            <h5 class="text-uppercase fw-bold mb-3">Metode Pengiriman</h5>
                            <div data-method-list></div>
                            <button type="button" class="btn btn-outline-success w-100 mt-2" data-fetch-rates>Cek Ongkir</button>
                            <div class="feedback error" data-rate-feedback></div>
                        </div>

                        <div class="feedback" data-form-feedback></div>
                        <button type="submit" class="btn-ogani mt-3" data-submit disabled>Lanjut ke Pembayaran</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="shipping-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="mt-4">
                        @foreach($cartSummary['items'] as $item)
                            <div class="summary-item">
                                <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                                <span>Rp {{ $item['subtotal_formatted'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>Rp {{ $checkoutTotals['subtotal_formatted'] ?? $cartSummary['total_price_formatted'] }}</span>
                    </div>
                    <div class="summary-item">
                        <span>Ongkir</span>
                        <span data-summary-shipping>Rp {{ $checkoutTotals['shipping_cost_formatted'] ?? '0' }}</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span data-summary-total>Rp {{ $checkoutTotals['grand_total_formatted'] ?? $cartSummary['total_price_formatted'] }}</span>
                    </div>
                    <p class="mt-3" style="color: rgba(255,255,255,0.7);">Biaya pengiriman dihitung otomatis setelah Anda memilih kurir.</p>
                </div>
            </div>
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
            ]) }};
        let selectedRate = initialSelection.rate || null;

        function setSubmitState() {
            if (submitButton) {
                submitButton.disabled = !selectedRate;
            }
        }

        function updateTotals(cost) {
            const shippingCost = parseInt(cost || 0, 10);
            if (summaryShipping) {
                summaryShipping.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(shippingCost);
            }
            const subtotal = {{ (int) $cartSummary['total_price'] }};
            const total = subtotal + shippingCost;
            if (summaryTotal) {
                summaryTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
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

        function renderMethods(rates) {
            if (!methodsContainer) return;
            methodsContainer.innerHTML = '';
            if (!rates || rates.length === 0) {
                methodsContainer.innerHTML = '<p class="text-muted">Tidak ada metode pengiriman tersedia.</p>';
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
                    <div>
                        <strong>${rate.courier_name || rate.courier} - ${rate.service}</strong>
                        <small class="text-muted">${rate.description || ''}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">Rp ${new Intl.NumberFormat('id-ID').format(rate.cost)}</div>
                        <small class="text-muted">${rate.etd ? rate.etd + ' hari' : ''}</small>
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
                showRateFeedback('Lengkapi alamat pengiriman terlebih dahulu.', true);
                return;
            }
            showRateFeedback('Mengambil ongkir...', false);
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
                renderMethods(data.data.rates || []);
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
                    showFormFeedback('Silakan pilih metode pengiriman terlebih dahulu.', 'error');
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
{{-- <script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script> auto fill kabupaten etc nt functioning if this uncomment --}}
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
