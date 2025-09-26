<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pesanan</title>
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
    <style>
        .hero-header {
            padding: 6rem 0 4rem;
        }
        .order-section {
            padding: 4rem 0;
        }
        .order-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.12);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
        }
        .order-card h2 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        .order-card p {
            margin-bottom: 0;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-pill.success { background: rgba(25, 135, 84, 0.12); color: #198754; }
        .status-pill.warning { background: rgba(255, 193, 7, 0.15); color: #ff9800; }
        .status-pill.info { background: rgba(13, 110, 253, 0.12); color: #0d6efd; }
        .order-table { width: 100%; }
        .order-table thead {
            background: var(--bs-dark);
            color: #fff;
        }
        .order-table th,
        .order-table td {
            padding: 1.1rem 1rem;
            vertical-align: middle;
        }
        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .order-item img {
            width: 78px;
            height: 78px;
            border-radius: 12px;
            object-fit: cover;
        }
        .feedback-alert {
            padding: 1.1rem 1.3rem;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .feedback-alert.success { background: rgba(25, 135, 84, 0.12); color: #198754; }
        .feedback-alert.info { background: rgba(13, 110, 253, 0.12); color: #0d6efd; }
        .feedback-alert.error { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
        .order-empty {
            text-align: center;
            padding: 3rem 1.5rem;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(15,23,42,0.1);
        }
        .order-empty a {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.8rem 1.8rem;
            border-radius: 999px;
            background: var(--bs-primary);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
@php
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-restoran';
    $orders = $orders ?? collect();
    $feedbackStatus = $feedbackStatus ?? null;
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

<div class="container-fluid bg-dark hero-header mb-5">
    <div class="container text-center my-5 pt-5 pb-4">
        <h1 class="display-3 text-white mb-3 animated slideInDown">Pesanan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center text-uppercase">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">Pesanan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container order-section">
    @if($feedbackStatus)
        <div class="feedback-alert {{ $feedbackStatus['type'] ?? 'info' }}">
            {{ $feedbackStatus['message'] ?? '' }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="order-empty">
            <h3 class="mb-3">Belum ada pesanan</h3>
            <p class="mb-0">Selesaikan proses checkout untuk melihat riwayat pesanan Anda.</p>
            <a href="{{ url('/produk') }}">Belanja Sekarang</a>
        </div>
    @else
        @foreach($orders as $order)
            @php
                $shippingStatus = optional($order->shipping)->status ?? 'packing';
                $statusLabel = 'Menunggu Konfirmasi';
                $statusClass = 'info';
                if($shippingEnabled) {
                    $statusLabel = match($shippingStatus) {
                        'delivered' => 'Tiba di tujuan',
                        'in_transit' => 'Dalam Pengiriman',
                        default => 'Sedang Diproses',
                    };
                    $statusClass = match($shippingStatus) {
                        'delivered' => 'success',
                        'in_transit' => 'warning',
                        default => 'info',
                    };
                } else {
                    $statusLabel = $order->is_reviewed ? 'Sudah dikonfirmasi admin' : 'Belum dikonfirmasi admin';
                    $statusClass = $order->is_reviewed ? 'success' : 'warning';
                }
                $orderStatus = $order->status === 'paid' ? 'Pembayaran diterima' : ucfirst($order->status);
            @endphp
            <div class="order-card">
                <div class="d-flex justify-content-between flex-wrap mb-4">
                    <div>
                        <h2>Pesanan #{{ $order->order_number }}</h2>
                        <p class="text-muted mb-0">Dibuat pada {{ $order->created_at?->format('d M Y H:i') }}</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-1">Status Pembayaran: {{ $orderStatus }}</p>
                        <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table order-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Status</th>
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
                                        <div class="order-item">
                                            <img src="{{ $imageUrl }}" alt="{{ $product->name ?? 'Produk' }}">
                                            <div>
                                                <a href="{{ $productUrl }}" class="fw-bold text-dark">{{ $product->name ?? 'Produk' }}</a>
                                                <div class="text-muted small">{{ $item->quantity }} x Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 mt-3 border-top">
                    <p class="mb-0 text-muted">Total Produk: {{ $order->items->sum('quantity') }}</p>
                    <h5 class="mb-0">Total Pembayaran: Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</h5>
                </div>
            </div>
        @endforeach
    @endif
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
</body>
</html>
