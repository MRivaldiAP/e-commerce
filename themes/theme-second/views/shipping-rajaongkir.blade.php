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
            background: #f4f6fb;
        }
        #shipping-page {
            padding: 3rem 2rem 4rem;
        }
        .shipping-layout {
            max-width: 1140px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
            gap: 2.2rem;
        }
        .shipping-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(34,51,84,0.12);
            padding: 2.2rem;
        }
        .shipping-card h2 {
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2a3d66;
        }
        .shipping-provider {
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
            color: #5a6d9a;
            font-size: 0.95rem;
        }
        .form-grid { display: grid; gap: 1.4rem; }
        .form-row { display: flex; gap: 1rem; }
        .form-field { flex: 1; }
        label { font-weight: 600; color: #2a3d66; display: block; margin-bottom: .5rem; }
        input, select, textarea {
            width: 100%;
            border-radius: 14px;
            border: 1px solid #d6dff6;
            background: #f8faff;
            padding: 0.9rem 1rem;
            font-size: 0.95rem;
        }
        textarea { min-height: 110px; resize: vertical; }
        .shipping-methods { display: grid; gap: 1rem; margin-top: 1.5rem; }
        .shipping-method {
            border: 1px solid #e0e7ff;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .shipping-method.active {
            border-color: #5160ff;
            box-shadow: 0 16px 30px rgba(81,96,255,0.15);
        }
        .summary-panel { display: grid; gap: 1.2rem; }
        .summary-items {
            border: 1px solid #e0e7ff;
            border-radius: 18px;
            background: linear-gradient(145deg, #ffffff, #f5f7ff);
            padding: 1.5rem;
        }
        .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: .9rem; }
        .summary-row:last-child { margin-bottom: 0; }
        .summary-row strong { color: #2a3d66; }
        .total-row { border-top: 1px dashed #c7d0f8; padding-top: 1rem; font-weight: 700; font-size: 1.2rem; color: #5160ff; }
        .checkout-button {
            border: none;
            border-radius: 14px;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #5160ff, #6774ff);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .alert { padding: .85rem 1rem; border-radius: 14px; font-weight: 600; }
        .alert-error { background: rgba(239,68,68,0.12); color: #b91c1c; border: 1px solid rgba(239,68,68,0.24); }
        .alert-info { background: rgba(59,130,246,0.1); color: #1d4ed8; border: 1px solid rgba(59,130,246,0.2); }
        .empty-state { padding: 1.5rem; text-align: center; border: 1px dashed #d6dff6; border-radius: 16px; color: #42558c; background: #f1f3ff; }
        @media (max-width: 992px) { .shipping-layout { grid-template-columns: 1fr; } }
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
    $shippingCost = old('shipping_cost', $shippingData['cost'] ?? 0);
    $gatewayLabel = $shippingGateway?->label() ?? 'RajaOngkir';
    $gatewayDescription = $shippingGateway?->description();
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
            <h2>Alamat Pengiriman &ndash; {{ $gatewayLabel }}</h2>
            <p class="shipping-provider">
                Pengiriman Anda akan dikelola oleh {{ $gatewayLabel }}@if(! empty($gatewayDescription)) &mdash; {{ $gatewayDescription }}@endif.
            </p>
            @if($errors->any())
                <div class="alert alert-error">Terdapat kesalahan pada formulir. Silakan periksa kembali.</div>
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
                            <label for="contact_phone">No. Telepon</label>
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
                        Lengkapi alamat pengiriman untuk melihat opsi layanan.
                    </div>
                </div>
            </form>
        </div>
        <aside class="shipping-card">
            <h2>Ringkasan Pesanan</h2>
            <div class="summary-panel">
                <div class="summary-items">
                    @foreach($cartSummary['items'] as $item)
                        <div class="summary-row">
                            <div>
                                <strong>{{ $item['name'] }}</strong>
                                <div class="small text-muted">x{{ $item['quantity'] }}</div>
                            </div>
                            <div>Rp {{ $item['subtotal_formatted'] }}</div>
                        </div>
                    @endforeach
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp {{ $cartSummary['total_price_formatted'] }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span id="shipping-cost-display">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span id="grand-total-display">Rp {{ number_format($cartSummary['total_price'] + $shippingCost, 0, ',', '.') }}</span>
                    </div>
                </div>
                <button type="submit" form="shipping-form" class="checkout-button">Lanjut ke Pembayaran</button>
                <div class="alert alert-info" id="shipping-feedback" style="display:none;"></div>
            </div>
        </aside>
    </div>
</section>

{!! view()->file(base_path('themes/' . $theme . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

{!! view()->file(base_path('themes/theme-herbalgreen/views/shipping-script-rajaongkir.blade.php'), [
    'cartSummary' => $cartSummary,
    'address' => $address,
    'selectedCourier' => $selectedCourier,
    'selectedService' => $selectedService,
])->render() !!}
</body>
</html>
