<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengiriman</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <style>
        body {
            background: #fff9f3;
        }
        #shipping-page {
            padding: 3rem 2rem;
        }
        .shipping-layout {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
            gap: 2rem;
        }
        .shipping-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(149, 89, 38, 0.12);
            padding: 2rem;
        }
        .shipping-card h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.45rem;
            color: #b75b26;
        }
        label {
            display: block;
            font-weight: 600;
            color: #7a4928;
            margin-bottom: .45rem;
        }
        input, select, textarea {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #f0d5c2;
            background: #fffaf6;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
        }
        textarea { min-height: 110px; resize: vertical; }
        .form-grid { display: grid; gap: 1.25rem; }
        .form-row { display: flex; gap: 1rem; }
        .form-field { flex: 1; }
        .shipping-methods { display: grid; gap: 1rem; margin-top: 1.5rem; }
        .shipping-method {
            border: 1px solid #f3d9c7;
            border-radius: 14px;
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .shipping-method.active {
            border-color: #b75b26;
            box-shadow: 0 10px 26px rgba(183, 91, 38, 0.18);
        }
        .summary-panel {
            display: grid;
            gap: 1rem;
        }
        .summary-items {
            border: 1px solid #f3d9c7;
            border-radius: 14px;
            padding: 1.5rem;
            background: #fffaf6;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .75rem;
        }
        .summary-row:last-child { margin-bottom: 0; }
        .total-row {
            border-top: 1px dashed #e3c3af;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.15rem;
            color: #b75b26;
        }
        .checkout-button {
            border: none;
            background: #b75b26;
            color: #fff;
            font-weight: 600;
            border-radius: 999px;
            padding: 1rem 1.5rem;
            width: 100%;
            cursor: pointer;
        }
        .alert {
            padding: .8rem 1rem;
            border-radius: 12px;
            font-weight: 600;
        }
        .alert-error { background: rgba(220, 53, 69, 0.12); color: #a52a2a; border: 1px solid rgba(220,53,69,0.24); }
        .alert-info { background: rgba(255, 193, 7, 0.16); color: #7a4c00; border: 1px solid rgba(255,193,7,0.28); }
        .empty-state { padding: 1.5rem; text-align: center; border: 1px dashed #f0d5c2; border-radius: 14px; color: #8b5a3c; background: #fff3e6; }
        @media (max-width: 992px) {
            .shipping-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@php
    use App\Support\LayoutSettings;

    $navigation = LayoutSettings::navigation($theme);
    $footerConfig = LayoutSettings::footer($theme);
    $contact = $shippingData['contact'] ?? [];
    $address = $shippingData['address'] ?? [];
    $selection = $shippingData['selection'] ?? [];
    $selectedCourier = old('shipping_courier', $selection['courier'] ?? '');
    $selectedService = old('shipping_service', $selection['service'] ?? '');
    $shippingCost = (float) old('shipping_cost', data_get($shippingData, 'cost', 0));
    $cartItems = data_get($cartSummary, 'items', []);
    $cartSubtotal = (float) data_get($cartSummary, 'total_price', 0);
    $cartSubtotalFormatted = data_get($cartSummary, 'total_price_formatted', number_format($cartSubtotal, 0, ',', '.'));
    $grandTotal = $cartSubtotal + $shippingCost;
    $grandTotalFormatted = number_format($grandTotal, 0, ',', '.');
@endphp

{!! view()->file(base_path('themes/' . $theme . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<section id="shipping-page">
    <div class="shipping-layout">
        <div class="shipping-card">
            <h2>Data Pengiriman</h2>
            @if ($errors->any())
                <div class="alert alert-error">Mohon lengkapi informasi yang dibutuhkan.</div>
            @endif
            <form id="shipping-form" method="POST" action="{{ route('checkout.shipping.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="contact_name">Nama Penerima</label>
                            <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', $contact['name'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label for="contact_phone">Telepon</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $contact['phone'] ?? '') }}" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="contact_email">Email</label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $contact['email'] ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label for="address">Alamat Lengkap</label>
                        <textarea id="address" name="address" required>{{ old('address', $address['street'] ?? '') }}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="province">Provinsi</label>
                            <select id="province" name="province_code" required>
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province['code'] }}" @selected(old('province_code', $address['province_code'] ?? '') === $province['code'])>{{ $province['name'] }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="province_name" name="province_name" value="{{ old('province_name', $address['province_name'] ?? '') }}">
                        </div>
                        <div class="form-field">
                            <label for="regency">Kota/Kabupaten</label>
                            <select id="regency" name="regency_code" required>
                                <option value="">Pilih Kota/Kabupaten</option>
                            </select>
                            <input type="hidden" id="regency_name" name="regency_name" value="{{ old('regency_name', $address['regency_name'] ?? '') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="district">Kecamatan</label>
                            <select id="district" name="district_code">
                                <option value="">Pilih Kecamatan</option>
                            </select>
                            <input type="hidden" id="district_name" name="district_name" value="{{ old('district_name', $address['district_name'] ?? '') }}">
                        </div>
                        <div class="form-field">
                            <label for="village">Kelurahan</label>
                            <select id="village" name="village_code">
                                <option value="">Pilih Kelurahan</option>
                            </select>
                            <input type="hidden" id="village_name" name="village_name" value="{{ old('village_name', $address['village_name'] ?? '') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="postal_code">Kode Pos</label>
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $address['postal_code'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label for="shipping_note">Catatan</label>
                            <input type="text" id="shipping_note" name="shipping_description" value="{{ old('shipping_description', $selection['description'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="shipping_courier" id="shipping_courier" value="{{ $selectedCourier }}">
                <input type="hidden" name="shipping_service" id="shipping_service" value="{{ $selectedService }}">
                <input type="hidden" name="shipping_cost" id="shipping_cost" value="{{ $shippingCost }}">
                <input type="hidden" name="shipping_etd" id="shipping_etd" value="{{ old('shipping_etd', $selection['etd'] ?? '') }}">

                <div class="shipping-methods" id="shipping-methods">
                    <div class="empty-state" data-methods-empty>
                        Lengkapi alamat untuk menampilkan opsi pengiriman.
                    </div>
                </div>
            </form>
        </div>
        <aside class="shipping-card">
            <h2>Ringkasan</h2>
            <div class="summary-panel">
                <div class="summary-items">
                    @foreach($cartItems as $item)
                        <div class="summary-row">
                            <div>
                                <strong>{{ data_get($item, 'name') }}</strong>
                                <div class="small text-muted">x{{ data_get($item, 'quantity') }}</div>
                            </div>
                            <div>Rp {{ data_get($item, 'subtotal_formatted') }}</div>
                        </div>
                    @endforeach
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp {{ $cartSubtotalFormatted }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span id="shipping-cost-display">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span id="grand-total-display">Rp {{ $grandTotalFormatted }}</span>
                    </div>
                </div>
                <button type="submit" form="shipping-form" class="checkout-button">Lanjut Pembayaran</button>
                <div class="alert alert-info" id="shipping-feedback" style="display:none;"></div>
            </div>
        </aside>
    </div>
</section>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

{!! view()->file(base_path('themes/theme-herbalgreen/views/shipping-script.blade.php'), [
    'cartSummary' => $cartSummary,
    'address' => $address,
    'selectedCourier' => $selectedCourier,
    'selectedService' => $selectedService,
])->render() !!}
</body>
</html>
