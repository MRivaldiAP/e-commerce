<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengiriman</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <style>
        #shipping-page {
            padding: 3rem 2rem;
            background: #f5fbf5;
        }
        .shipping-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.25fr);
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }
        .shipping-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        .shipping-card h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            color: var(--color-primary);
        }
        .shipping-provider {
            margin-top: -0.75rem;
            margin-bottom: 1.5rem;
            color: #4f6f58;
            font-size: 0.95rem;
        }
        .form-grid {
            display: grid;
            gap: 1.25rem;
        }
        .form-row {
            display: flex;
            gap: 1rem;
        }
        .form-row .form-field {
            flex: 1;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: .5rem;
            color: #1f3c2f;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: 1px solid #d9e6de;
            background: #fdfdfd;
            font-size: 0.95rem;
        }
        textarea {
            min-height: 110px;
            resize: vertical;
        }
        .shipping-methods {
            margin-top: 1.5rem;
            display: grid;
            gap: 1rem;
        }
        .shipping-method {
            border: 1px solid #dcefe2;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .shipping-method.active {
            border-color: var(--color-primary);
            box-shadow: 0 8px 24px rgba(67,160,71,0.18);
        }
        .shipping-method input {
            margin-right: 1rem;
        }
        .shipping-summary {
            display: grid;
            gap: 1rem;
        }
        .summary-items {
            border: 1px solid #e4efe7;
            border-radius: 16px;
            padding: 1.5rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .75rem;
        }
        .summary-row:last-child {
            margin-bottom: 0;
        }
        .summary-row strong {
            color: #1f3c2f;
        }
        .total-row {
            border-top: 1px dashed #cbe2d1;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        .checkout-button {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 1rem 1.5rem;
            background: var(--color-primary);
            color: #fff;
            font-weight: 600;
            margin-top: 1.5rem;
            cursor: pointer;
            transition: transform .2s ease;
        }
        .checkout-button:hover {
            transform: translateY(-1px);
        }
        .alert {
            padding: .8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .alert-error {
            background: rgba(220,53,69,0.12);
            color: #a31d2a;
            border: 1px solid rgba(220,53,69,0.2);
        }
        .alert-info {
            background: rgba(33,150,243,0.12);
            color: #0d47a1;
            border: 1px solid rgba(33,150,243,0.24);
        }
        .empty-state {
            padding: 2rem;
            border-radius: 14px;
            background: #f9fbf9;
            text-align: center;
            border: 1px dashed #cde3d2;
            color: #4f6f58;
        }
        @media (max-width: 992px) {
            .shipping-layout {
                grid-template-columns: 1fr;
            }
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
            <h2>Informasi Pengiriman &ndash; {{ $gatewayLabel }}</h2>
            <p class="shipping-provider">
                Pengiriman diproses melalui {{ $gatewayLabel }}@if(! empty($gatewayDescription)) &mdash; {{ $gatewayDescription }}@endif.
            </p>
            @if ($errors->any())
                <div class="alert alert-error">
                    Mohon periksa kembali isian Anda.
                </div>
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
                            <input type="hidden" name="province_name" id="province_name" value="{{ old('province_name', $address['province_name'] ?? '') }}">
                        </div>
                        <div class="form-field">
                            <label for="regency">Kota/Kabupaten</label>
                            <select id="regency" name="regency_code" required>
                                <option value="">Pilih Kota/Kabupaten</option>
                            </select>
                            <input type="hidden" name="regency_name" id="regency_name" value="{{ old('regency_name', $address['regency_name'] ?? '') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="district">Kecamatan</label>
                            <select id="district" name="district_code">
                                <option value="">Pilih Kecamatan</option>
                            </select>
                            <input type="hidden" name="district_name" id="district_name" value="{{ old('district_name', $address['district_name'] ?? '') }}">
                        </div>
                        <div class="form-field">
                            <label for="village">Kelurahan</label>
                            <select id="village" name="village_code">
                                <option value="">Pilih Kelurahan</option>
                            </select>
                            <input type="hidden" name="village_name" id="village_name" value="{{ old('village_name', $address['village_name'] ?? '') }}">
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
                        Pilih alamat lengkap untuk melihat pilihan pengiriman.
                    </div>
                </div>
            </form>
        </div>
        <aside class="shipping-card">
            <h2>Ringkasan Pesanan</h2>
            <div class="shipping-summary">
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
