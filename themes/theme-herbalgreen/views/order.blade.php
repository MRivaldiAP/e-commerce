<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <style>
        #orders {
            padding: 4rem 2rem;
            background: #f7f7f7;
        }
        #orders h1 {
            text-align: center;
            margin-bottom: 0.5rem;
        }
        #orders p.subtitle {
            text-align: center;
            color: #4a4a4a;
            margin-bottom: 2.5rem;
        }
        .feedback-banner {
            margin: 0 auto 2rem;
            max-width: 920px;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
        }
        .feedback-banner.success { background: rgba(76,175,80,0.12); color: #2e7d32; }
        .feedback-banner.info { background: rgba(33,150,243,0.12); color: #1e88e5; }
        .feedback-banner.error { background: rgba(244,67,54,0.12); color: #c62828; }
        .order-wrapper {
            max-width: 1100px;
            margin: 0 auto 2rem;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(15,23,42,0.08);
            padding: 2rem;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .order-header h2 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--color-primary);
        }
        .order-meta {
            font-size: 0.95rem;
            color: #6f6f6f;
        }
        .order-status {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .order-status.success { background: rgba(76,175,80,0.15); color: #2e7d32; }
        .order-status.warning { background: rgba(255,193,7,0.15); color: #c28704; }
        .order-status.info { background: rgba(33,150,243,0.12); color: #1e88e5; }
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-table thead {
            background: var(--color-primary);
            color: #fff;
        }
        .order-table th,
        .order-table td {
            padding: 1rem 1.2rem;
            text-align: left;
        }
        .order-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .order-item img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .order-empty {
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 3rem 2rem;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(15,23,42,0.08);
        }
        .order-empty a {
            display: inline-block;
            margin-top: 1.5rem;
            background: var(--color-primary);
            color: #fff;
            padding: 0.8rem 1.6rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
@php
    $orders = $orders ?? collect();
    $feedbackStatus = $feedbackStatus ?? null;
    $cartSummary = App\Support\Cart::summary();
    $links = [
        ['label' => 'Home', 'href' => url('/'), 'visible' => true],
        ['label' => 'Produk', 'href' => url('/produk'), 'visible' => true],
        ['label' => 'Keranjang', 'href' => url('/keranjang'), 'visible' => true],
        ['label' => 'Pesanan', 'href' => url('/pesanan'), 'visible' => true],
    ];
@endphp

{!! view()->file(base_path('themes/theme-herbalgreen/views/components/nav-menu.blade.php'), ['links' => $links, 'cart' => $cartSummary])->render() !!}

<section id="orders">
    <h1>Pesanan</h1>
    <p class="subtitle">Pantau status pesanan dan pembayaran Anda.</p>

    @if($feedbackStatus)
        <div class="feedback-banner {{ $feedbackStatus['type'] ?? 'info' }}">
            {{ $feedbackStatus['message'] ?? '' }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="order-empty">
            <h3>Belum ada pesanan</h3>
            <p>Segera lengkapi proses checkout untuk melihat pesanan Anda di sini.</p>
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
                        'delivered' => 'Pesanan Terkirim',
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
            <div class="order-wrapper">
                <div class="order-header">
                    <div>
                        <h2>Pesanan #{{ $order->order_number }}</h2>
                        <div class="order-meta">Dibuat pada {{ $order->created_at?->format('d M Y H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="order-meta">Status Pembayaran: {{ $orderStatus }}</div>
                        <span class="order-status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                <table class="order-table">
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
                                            <a href="{{ $productUrl }}" class="text-bold" style="color: var(--color-primary);">{{ $product->name ?? 'Produk' }}</a>
                                            <div class="order-meta">{{ $item->quantity }} x Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <span class="order-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="order-summary">
                    <div class="order-meta">Total Produk: {{ $order->items->sum('quantity') }}</div>
                    <div class="order-meta">Total Pembayaran: <strong>Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</strong></div>
                </div>
            </div>
        @endforeach
    @endif
</section>

{!! view()->file(base_path('themes/theme-herbalgreen/views/components/footer.blade.php'))->render() !!}
</body>
</html>
