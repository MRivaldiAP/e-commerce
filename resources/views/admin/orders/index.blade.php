@extends('layout.admin')
@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Pesanan</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
          <li class="breadcrumb-item active" aria-current="page">Pesanan</li>
        </ol>
      </nav>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
      <div class="col-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="card-title mb-0">Daftar Pesanan</h4>
                <small class="text-muted">Pantau status pembayaran dan pengiriman pelanggan Anda.</small>
              </div>
              <div class="text-right">
                <span class="badge badge-outline-primary">Pembayaran: {{ $paymentEnabled ? 'Aktif' : 'Tidak Aktif' }}</span>
                <span class="badge badge-outline-success ml-2">Pengiriman: {{ $shippingEnabled ? 'Aktif' : 'Tidak Aktif' }}</span>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Nomor Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Ringkasan</th>
                    <th>Dibuat</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($orders as $order)
                    <tr>
                      <td>
                        <strong>{{ $order->order_number }}</strong>
                        <div class="small text-muted">#{{ $order->id }}</div>
                      </td>
                      <td>
                        <div class="font-weight-semibold">{{ $order->user->name ?? 'Pengguna' }}</div>
                        <div class="small text-muted">{{ $order->user->email ?? '-' }}</div>
                      </td>
                      <td>Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</td>
                      <td>
                        @php
                          $payment = $order->payment;
                          $status = optional($payment)->status ?? 'pending';
                          $statusLabel = [
                            'pending' => 'badge-warning',
                            'success' => 'badge-success',
                            'failed' => 'badge-danger',
                          ][$status] ?? 'badge-secondary';
                        @endphp
                        @if($payment)
                          <div class="badge {{ $statusLabel }} text-uppercase mb-1">{{ $payment->status }}</div>
                          <div class="small">Metode: {{ strtoupper($payment->method) }}</div>
                          <div class="small">Transaksi: {{ $payment->transaction_id ?? '-' }}</div>
                        @else
                          <span class="badge badge-secondary">Belum ada</span>
                        @endif
                      </td>
                      <td>
                        @if($shippingEnabled)
                          @php
                            $shippingData = $order->shipping;
                            $shippingStatus = optional($shippingData)->status ?? 'packing';
                            $shippingLabel = [
                              'packing' => 'badge-info',
                              'pending' => 'badge-info',
                              'in_transit' => 'badge-warning',
                              'delivered' => 'badge-success',
                              'cancelled' => 'badge-danger',
                            ][$shippingStatus] ?? 'badge-secondary';
                            $shippingData = $order->shipping;
                            $oldOrderId = old('order_id');
                          @endphp
                          <span class="badge {{ $shippingLabel }} text-capitalize">{{ str_replace('_', ' ', $shippingStatus) }}</span>
                          <form method="POST" action="{{ route('admin.orders.shipping', $order) }}" class="mt-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <div class="form-row align-items-end">
                              <div class="form-group col-md-4">
                                <label class="small text-muted d-block">Kurir</label>
                                <input type="text" name="courier" class="form-control form-control-sm" value="{{ $oldOrderId == $order->id ? old('courier') : ($shippingData->courier ?? '') }}" required>
                              </div>
                              <div class="form-group col-md-4">
                                <label class="small text-muted d-block">Layanan</label>
                                <input type="text" name="service" class="form-control form-control-sm" value="{{ $oldOrderId == $order->id ? old('service') : ($shippingData->service ?? '') }}">
                              </div>
                              <div class="form-group col-md-4">
                                <label class="small text-muted d-block">No. Resi</label>
                                <input type="text" name="tracking_number" class="form-control form-control-sm" value="{{ $oldOrderId == $order->id ? old('tracking_number') : ($shippingData->tracking_number ?? '') }}">
                              </div>
                            </div>
                            <div class="form-row align-items-end">
                              <div class="form-group col-md-4">
                                <label class="small text-muted d-block">Status</label>
                                @php
                                  $statusValue = $oldOrderId == $order->id ? old('status', $shippingStatus) : $shippingStatus;
                                @endphp
                                <select name="status" class="form-control form-control-sm">
                                  <option value="packing" {{ $statusValue === 'packing' ? 'selected' : '' }}>Packing</option>
                                  <option value="in_transit" {{ $statusValue === 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan</option>
                                  <option value="delivered" {{ $statusValue === 'delivered' ? 'selected' : '' }}>Terkirim</option>
                                  <option value="cancelled" {{ $statusValue === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                              </div>
                              <div class="form-group col-md-5">
                                <label class="small text-muted d-block">Estimasi Tiba</label>
                                <input type="date" name="estimated_delivery" class="form-control form-control-sm" value="{{ $oldOrderId == $order->id ? old('estimated_delivery') : optional($shippingData?->estimated_delivery)->format('Y-m-d') }}">
                              </div>
                              <div class="form-group col-md-3 text-right">
                                <button type="submit" class="btn btn-sm btn-outline-primary mt-3">Simpan</button>
                              </div>
                            </div>
                          </form>
                        @else
                          <form method="POST" action="{{ route('admin.orders.review', $order) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $order->is_reviewed ? 'btn-success' : 'btn-outline-secondary' }}">
                              {{ $order->is_reviewed ? 'Diterima' : 'Belum' }}
                            </button>
                          </form>
                        @endif
                      </td>
                      <td style="max-width: 240px;">
                        <ul class="pl-3 mb-0 small">
                          @foreach($order->items as $item)
                            <li>{{ $item->product->name ?? 'Produk' }} ({{ $item->quantity }}x)</li>
                          @endforeach
                        </ul>
                      </td>
                      <td>
                        <div>{{ $order->created_at?->format('d M Y') }}</div>
                        <div class="small text-muted">{{ $order->created_at?->format('H:i') }}</div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center">Belum ada pesanan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
              <div>
                <small class="text-muted">Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() ?? 0 }} pesanan</small>
              </div>
              <div>
                {{ $orders->appends(request()->query())->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
