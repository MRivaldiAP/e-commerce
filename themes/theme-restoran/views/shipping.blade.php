@php
    $themeName = $theme ?? 'theme-restoran';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pengiriman</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
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
        .shipping-wrapper {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .shipping-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            padding: 40px;
        }
        .shipping-card h2 {
            font-family: 'Pacifico', cursive;
            font-size: 32px;
            color: var(--bs-primary);
            margin-bottom: 24px;
        }
        .form-control,
        .form-select {
            padding: 14px 16px;
            border-radius: 12px;
            border-color: rgba(0,0,0,0.08);
        }
        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(254, 161, 22, 0.15);
        }
        .method-option {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .method-option.active {
            border-color: var(--bs-primary);
            box-shadow: 0 10px 20px rgba(254,161,22,0.15);
        }
        .method-option input { display: none; }
        .summary-card {
            background: var(--theme-accent);
            color: #fff;
            border-radius: 16px;
            padding: 40px;
            height: 100%;
        }
        .summary-card h2 {
            font-family: 'Pacifico', cursive;
            font-size: 30px;
            color: #fff;
        }
        .summary-card ul {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }
        .summary-card li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 16px;
        }
        .summary-card li.total {
            font-size: 22px;
            font-weight: 700;
            color: var(--bs-primary);
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 8px;
        }
        .product-item .promo-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 999px;
            padding: 0.15rem 0.6rem;
            font-size: 0.65rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 0.3rem;
        }
        .price-stack {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            align-items: flex-end;
        }
        .price-stack .price-original {
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }
        .price-stack .price-current {
            color: #fff;
            font-weight: 700;
        }
        .btn-primary {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            border-radius: 30px;
            padding: 14px 18px;
            font-weight: 600;
        }
        .feedback {
            display: none;
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 12px;
        }
        .feedback.visible { display: block; }
        .feedback.error { background: #fff4f4; color: #c53030; }
        .feedback.success { background: #e8f9f3; color: #0f766e; }
    </style>
</head>
<body>
@php
    use App\Support\LayoutSettings;
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $contact = $shippingData['contact'] ?? [];
    $addressData = $shippingData['address'] ?? [];
    $selectedRate = $shippingData['selected_rate'] ?? null;
    $couriers = $shippingConfig['couriers'] ?? [];
@endphp

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<div class="container-fluid shipping-wrapper">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="shipping-card">
                    <h2>Detail Pengiriman</h2>
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
                            <h5 class="fw-bold mb-3">Pilih Metode Pengiriman</h5>
                            <div data-method-list></div>
                            <button type="button" class="btn btn-outline-primary w-100 mt-2" data-fetch-rates>Cek Ongkir</button>
                            <div class="feedback error" data-rate-feedback></div>
                        </div>

                        <div class="feedback" data-form-feedback></div>
                        <button type="submit" class="btn btn-primary w-100 mt-3" data-submit disabled>Lanjut ke Pembayaran</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="summary-card">
                    <h2>Ringkasan Pesanan</h2>
                    <div class="mt-4">
                        @foreach($cartSummary['items'] as $item)
                            <div class="product-item">
                                <div class="d-flex flex-column">
                                    <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                                    @if(!empty($item['has_promo']) && !empty($item['promo_label']))
                                        <span class="promo-label">{{ $item['promo_label'] }}</span>
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
                    <p class="mb-0 text-white-50">Biaya ongkir dihitung otomatis berdasarkan kurir pilihan dan lokasi Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>

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
<script src="{{ asset('storage/themes/theme-restoran/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
