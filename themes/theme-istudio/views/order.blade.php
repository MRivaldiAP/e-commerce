<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
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
        .hero-header {
            background-size: cover;
            background-position: center;
            position: relative;
            padding: 120px 0 80px;
        }
        .hero-header::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 180, 0, 0.35), transparent 55%);
            pointer-events: none;
        }
        .hero-header h1 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #fff;
        }
        .hero-header p {
            color: rgba(255, 255, 255, 0.75);
            max-width: 540px;
        }
        .hero-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
        }
        .hero-breadcrumb a { color: inherit; text-decoration: none; }
        .orders-section { margin-top: -60px; position: relative; z-index: 5; }
        .orders-section .container { padding-top: 4rem; padding-bottom: 4rem; }
        .order-card {
            background: #fff;
            border-radius: 24px;
            padding: 32px 36px;
            box-shadow: 0 35px 60px rgba(15, 23, 43, 0.1);
            margin-bottom: 32px;
        }
        .order-card__header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-start;
        }
        .order-card__title {
            font-size: 1.1rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #0f172b;
            margin-bottom: 0.35rem;
        }
        .order-card__meta { color: #6b7280; font-size: 0.9rem; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .status-badge.success { background: rgba(34, 197, 94, 0.15); color: #0f8a44; }
        .status-badge.warning { background: rgba(250, 204, 21, 0.18); color: #a36a00; }
        .status-badge.info { background: rgba(59, 130, 246, 0.18); color: #1f67c7; }
        .status-badge.danger { background: rgba(239, 68, 68, 0.18); color: #b91c1c; }
        .order-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .order-table thead th {
            font-size: 0.75rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #6c757d;
            padding-bottom: 12px;
        }
        .order-table tbody td {
            padding: 18px 0;
            border-top: 1px solid rgba(15, 23, 43, 0.08);
            vertical-align: middle;
        }
        .order-product {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .order-product img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 18px;
        }
        .order-product a { color: #0f172b; font-weight: 600; text-decoration: none; }
        .order-footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 24px;
            align-items: center;
        }
        .order-summary {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .order-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172b;
            letter-spacing: .06em;
        }
        .tracking-widget {
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 43, 0.08);
            border-radius: 16px;
            padding: 16px 20px;
            margin-top: 18px;
        }
        .tracking-widget button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.4rem;
            border-radius: 999px;
            border: none;
            background: #ffb400;
            color: #0f172b;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .tracking-result { margin-top: 0.75rem; font-size: 0.9rem; color: #4b5563; }
        .order-empty {
            background: #fff;
            border-radius: 24px;
            padding: 72px 48px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(15, 23, 43, 0.08);
        }
        .order-empty h3 {
            font-size: 2rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #0f172b;
        }
        .order-empty p { color: #6b7280; max-width: 460px; margin: 12px auto 0; }
        .order-empty a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.85rem 1.6rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: #ffb400;
            color: #0f172b;
            margin-top: 28px;
        }
        .alert-feedback {
            border-radius: 18px;
            padding: 16px 22px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        .alert-feedback.success { background: rgba(34, 197, 94, 0.15); color: #0f8a44; }
        .alert-feedback.info { background: rgba(59, 130, 246, 0.15); color: #1f67c7; }
        .alert-feedback.error { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
        @media (max-width: 991px) {
            .hero-header { padding-top: 100px; padding-bottom: 70px; }
            .orders-section { margin-top: -40px; }
            .order-card { padding: 28px 24px; }
        }
    </style>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-istudio';
    $settings = PageSetting::forPage('order');
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));

    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroBackground = $resolveMedia($settings['hero.background'] ?? null, $assetBase('img/hero-slider-1.jpg'));
    $heroHeading = $settings['hero.heading'] ?? 'Pesanan Saya';
    $heroDescription = $settings['hero.description'] ?? 'Pantau status pembayaran dan pengiriman terbaru untuk pesanan Anda.';

    $orders = collect($orders ?? []);
    $shippingEnabled = $shippingEnabled ?? false;
    $emptyTitle = $settings['empty.title'] ?? 'Belum ada pesanan';
    $emptyDescription = $settings['empty.description'] ?? 'Pesanan Anda akan tampil di sini setelah proses checkout berhasil.';
    $emptyButton = $settings['empty.button'] ?? 'Mulai Belanja';
@endphp

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if($heroVisible)
    <section class="container-fluid hero-header" style="background-image: url('{{ $heroBackground }}');">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h1>{{ $heroHeading }}</h1>
                    @if(!empty($heroDescription))
                        <p class="mb-0">{{ $heroDescription }}</p>
                    @endif
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="hero-breadcrumb">
                        <a href="{{ url('/') }}">Home</a>
                        <span>/</span>
                        <span>{{ $heroHeading }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

<section class="orders-section">
    <div class="container">
        @if(!empty($feedbackStatus))
            <div class="alert-feedback {{ $feedbackStatus['type'] ?? 'info' }}">
                {{ $feedbackStatus['message'] ?? '' }}
            </div>
        @endif

        @if($orders->isEmpty())
            <div class="order-empty">
                <h3>{{ $emptyTitle }}</h3>
                <p>{{ $emptyDescription }}</p>
                <a href="{{ url('/produk') }}">{{ $emptyButton }}</a>
            </div>
        @else
            @foreach($orders as $order)
                @php
                    $shippingData = optional($order->shipping);
                    $shippingStatus = $shippingData->status ?? 'pending';
                    $remoteOrderCreated = $shippingEnabled && ! empty($shippingData?->remote_id);
                    $trackingNumberAvailable = $shippingEnabled && ! empty($shippingData?->tracking_number);
                    $statusLabel = 'Menunggu Konfirmasi';
                    $statusClass = 'info';

                    if ($shippingEnabled) {
                        if ($remoteOrderCreated && $trackingNumberAvailable) {
                            $statusLabel = match($shippingStatus) {
                                'delivered' => 'Selesai',
                                'in_transit' => 'Sedang Dikirim',
                                'cancelled' => 'Dibatalkan',
                                default => 'Dalam Pengiriman',
                            };
                            $statusClass = match($shippingStatus) {
                                'delivered' => 'success',
                                'in_transit' => 'warning',
                                'cancelled' => 'danger',
                                default => 'info',
                            };
                        } elseif ($remoteOrderCreated) {
                            $statusLabel = 'Menunggu nomor resi';
                            $statusClass = 'warning';
                        } else {
                            $statusLabel = 'Sedang disiapkan penjual';
                            $statusClass = 'info';
                        }
                    } else {
                        $statusLabel = $order->is_reviewed ? 'Sudah dikonfirmasi' : 'Belum dikonfirmasi';
                        $statusClass = $order->is_reviewed ? 'success' : 'warning';
                    }

                    $orderStatus = $order->status === 'paid' ? 'Pembayaran diterima' : ucfirst($order->status);
                    $trackingReady = $shippingEnabled && $remoteOrderCreated && $trackingNumberAvailable;
                @endphp
                <article class="order-card">
                    <header class="order-card__header">
                        <div>
                            <div class="order-card__title">Pesanan #{{ $order->order_number }}</div>
                            <div class="order-card__meta">Dibuat pada {{ $order->created_at?->format('d M Y H:i') }}</div>
                        </div>
                        <div class="text-lg-end">
                            <div class="order-card__meta mb-2">Status Pembayaran: {{ $orderStatus }}</div>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th class="text-start">Produk</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $product = $item->product;
                                        $imagePath = optional($product?->images?->first())->path;
                                        $imageUrl = $imagePath ? asset('storage/' . $imagePath) : 'https://via.placeholder.com/160x160?text=Produk';
                                        $productUrl = $product ? route('products.show', $product->id) : '#';
                                        $subtotal = (int) $item->price * (int) $item->quantity;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="order-product">
                                                <img src="{{ $imageUrl }}" alt="{{ $product->name ?? 'Produk' }}">
                                                <div>
                                                    <a href="{{ $productUrl }}">{{ $product->name ?? 'Produk' }}</a>
                                                    <div class="order-card__meta">{{ $item->quantity }} x Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                        <td class="text-end"><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <footer class="order-footer">
                        <div class="order-summary">
                            <div>Total Item: {{ $order->items->sum('quantity') }}</div>
                            @if($shippingEnabled)
                                <div>Kurir: {{ strtoupper($shippingData->courier ?? 'Belum ditentukan') }} {{ $shippingData->service ? '(' . $shippingData->service . ')' : '' }}</div>
                                <div>Ongkir: Rp {{ number_format($shippingData->cost ?? 0, 0, ',', '.') }}</div>
                                <div>Nomor Resi: {{ $shippingData->tracking_number ?? 'Belum tersedia' }}</div>
                                <div>Status Pengiriman: {{ ucfirst(str_replace('_', ' ', $shippingData->status ?? 'pending')) }}</div>
                                @if($trackingReady)
                                    <div class="tracking-widget" data-tracking-widget>
                                        <div class="tracking-result">Lihat pembaruan status pengiriman Anda di sini.</div>
                                        <button type="button" data-track-button data-tracking-number="{{ $shippingData->tracking_number }}" data-courier="{{ $shippingData->courier }}">
                                            <i class="bi bi-truck"></i>
                                            Lacak Pengiriman
                                        </button>
                                        <div class="tracking-result" data-tracking-result hidden></div>
                                    </div>
                                @elseif(! $remoteOrderCreated)
                                    <div class="tracking-widget">
                                        <div class="tracking-result">Pesanan sedang diproses. Pelacakan tersedia setelah dikirim.</div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="order-total">Total Pembayaran: Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</div>
                    </footer>
                </article>
            @endforeach
        @endif
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
    'brand' => $navigation['brand'],
])->render() !!}

<script>
    document.querySelectorAll('[data-track-button]').forEach(function(button) {
        button.addEventListener('click', function() {
            const widget = button.closest('[data-tracking-widget]');
            const resultBox = widget?.querySelector('[data-tracking-result]');
            if (! resultBox) {
                return;
            }

            resultBox.hidden = false;
            resultBox.textContent = 'Mengambil status pelacakan...';

            fetch('{{ route('shipping.track') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tracking_number: button.getAttribute('data-tracking-number'),
                    courier: button.getAttribute('data-courier')
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(payload) {
                if (payload.status === 'ok' && payload.data && payload.data.summary) {
                    const summary = payload.data.summary;
                    const note = summary.note ? ' (' + summary.note + ')' : '';
                    resultBox.textContent = 'Status: ' + (summary.status || 'Tidak diketahui') + note;
                } else {
                    resultBox.textContent = payload.message || 'Gagal mengambil status pelacakan.';
                }
            })
            .catch(function() {
                resultBox.textContent = 'Tidak dapat terhubung ke layanan pelacakan.';
            });
        });
    });
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
