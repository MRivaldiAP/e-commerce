<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        .breadcrumb__text h1 {
            font-size: 46px;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .order-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .order-card__header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .order-card__title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1c1c1c;
        }
        .order-card__meta {
            font-size: 0.95rem;
            color: #6f6f6f;
        }
        .order-table thead {
            background: #f5f5f5;
        }
        .order-table thead th {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 1rem 1.5rem;
        }
        .order-table tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f2f2f2;
        }
        .order-product {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .order-product img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-badge--success {
            background: rgba(127, 173, 57, 0.15);
            color: #5c8d1b;
        }
        .status-badge--warning {
            background: rgba(255, 193, 7, 0.15);
            color: #c28704;
        }
        .status-badge--info {
            background: rgba(52, 152, 219, 0.12);
            color: #1f6fa8;
        }
        .status-badge--danger {
            background: rgba(220, 53, 69, 0.15);
            color: #a01e2c;
        }
        .order-empty {
            text-align: center;
            padding: 3rem 1rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        }
        .alert-feedback {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .alert-feedback.success { background: rgba(76,175,80,0.12); color: #2e7d32; }
        .alert-feedback.info { background: rgba(30,136,229,0.12); color: #1e88e5; }
        .alert-feedback.error { background: rgba(244,67,54,0.12); color: #c62828; }
        .tracking-widget {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            background: #f7f7f7;
            border: 1px solid #e5e7eb;
        }
        .tracking-widget button {
            display: inline-block;
            border: none;
            background: #7fad39;
            color: #fff;
            padding: 0.6rem 1.4rem;
            border-radius: 999px;
            font-weight: 600;
        }
        .tracking-widget button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .tracking-result {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: #4a4a4a;
        }
    </style>
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-second';
    $orders = $orders ?? collect();
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
@endphp

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

<section class="breadcrumb-section set-bg" data-setbg="{{ asset('storage/themes/theme-second/img/breadcrumb.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h1>Pesanan</h1>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <span>Pesanan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="shoping-cart spad" style="padding-top: 50px;">
    <div class="container">
        @if(!empty($feedbackStatus))
            <div class="alert-feedback {{ $feedbackStatus['type'] ?? 'info' }}">
                {{ $feedbackStatus['message'] ?? '' }}
            </div>
        @endif

        @if($orders->isEmpty())
            <div class="order-empty">
                <h4>Belum ada pesanan</h4>
                <p>Pesanan Anda akan tampil di sini setelah melakukan checkout.</p>
                <a href="{{ url('/produk') }}" class="primary-btn">Mulai Belanja</a>
            </div>
        @else
            @foreach($orders as $order)
                @php
                    $shippingData = optional($order->shipping);
                    $shippingStatus = $shippingData->status ?? 'pending';
                    $remoteOrderCreated = $shippingEnabled && ! empty($shippingData?->remote_id);
                    $trackingNumberAvailable = $shippingEnabled && ! empty($shippingData?->tracking_number);
                    $statusLabel = 'Menunggu Konfirmasi';
                    $statusClass = 'status-badge--info';
                    if($shippingEnabled) {
                        if($remoteOrderCreated && $trackingNumberAvailable) {
                            $statusLabel = match($shippingStatus) {
                                'delivered' => 'Selesai',
                                'in_transit' => 'Sedang Dikirim',
                                'cancelled' => 'Dibatalkan',
                                default => 'Sedang Dalam Pengiriman',
                            };
                            $statusClass = match($shippingStatus) {
                                'delivered' => 'status-badge--success',
                                'in_transit' => 'status-badge--warning',
                                'cancelled' => 'status-badge--danger',
                                default => 'status-badge--info',
                            };
                        } elseif($remoteOrderCreated) {
                            $statusLabel = 'Menunggu nomor resi';
                            $statusClass = 'status-badge--warning';
                        } else {
                            $statusLabel = 'Sedang disiapkan penjual';
                            $statusClass = 'status-badge--info';
                        }
                    } else {
                        $statusLabel = $order->is_reviewed ? 'Sudah dikonfirmasi' : 'Belum dikonfirmasi';
                        $statusClass = $order->is_reviewed ? 'status-badge--success' : 'status-badge--warning';
                    }
                    $orderStatus = $order->status === 'paid' ? 'Pembayaran diterima' : ucfirst($order->status);
                    $trackingReady = $shippingEnabled && $remoteOrderCreated && $trackingNumberAvailable;
                @endphp
                <div class="order-card">
                    <div class="order-card__header">
                        <div>
                            <div class="order-card__title">Pesanan #{{ $order->order_number }}</div>
                            <div class="order-card__meta">Dibuat pada {{ $order->created_at?->format('d M Y H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="order-card__meta">Status Pembayaran: {{ $orderStatus }}</div>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table order-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $product = $item->product;
                                        $imagePath = optional($product?->images?->first())->path;
                                        $imageUrl = $imagePath ? asset('storage/' . $imagePath) : 'https://via.placeholder.com/120x120?text=No+Image';
                                        $productUrl = $product ? route('products.show', $product->id) : '#';
                                        $subtotal = (int) $item->price * (int) $item->quantity;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="order-product">
                                                <img src="{{ $imageUrl }}" alt="{{ $product->name ?? 'Produk' }}">
                                                <div>
                                                    <a href="{{ $productUrl }}" class="font-weight-bold text-dark">{{ $product->name ?? 'Produk' }}</a>
                                                    <div class="text-muted small">{{ $item->quantity }} x Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                        <td class="text-right">
                                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <div class="text-muted">Total Item: {{ $order->items->sum('quantity') }}</div>
                            @if($shippingEnabled)
                                <div class="text-muted">Kurir: {{ strtoupper($shippingData->courier ?? 'Belum ditentukan') }} {{ $shippingData->service ? '(' . $shippingData->service . ')' : '' }}</div>
                                <div class="text-muted">Ongkir: Rp {{ number_format($shippingData->cost ?? 0, 0, ',', '.') }}</div>
                                <div class="text-muted">Nomor Resi: {{ $shippingData->tracking_number ?? 'Belum tersedia' }}</div>
                                <div class="text-muted">Status Pengiriman: {{ ucfirst(str_replace('_', ' ', $shippingData->status ?? 'pending')) }}</div>
                                @if($trackingReady)
                                    <div class="tracking-widget" data-tracking-widget>
                                        <div class="tracking-result">Lihat status terbaru dengan menekan tombol berikut.</div>
                                        <button type="button" data-track-button data-tracking-number="{{ $shippingData->tracking_number }}" data-courier="{{ $shippingData->courier }}">Lacak Pengiriman</button>
                                        <div class="tracking-result" data-tracking-result hidden></div>
                                    </div>
                                @elseif(! $remoteOrderCreated)
                                    <div class="tracking-widget">
                                        <div class="tracking-result">Sedang disiapkan penjual. Pelacakan tersedia setelah pesanan dikirim.</div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="h5 mb-0">Total Pembayaran: Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</section>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>
<script>
    document.querySelectorAll('[data-track-button]').forEach(function(button) {
        button.addEventListener('click', function() {
            var widget = button.closest('[data-tracking-widget]');
            var resultBox = widget.querySelector('[data-tracking-result]');
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
                    var summary = payload.data.summary;
                    var note = summary.note ? ' (' + summary.note + ')' : '';
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
</body>
</html>
