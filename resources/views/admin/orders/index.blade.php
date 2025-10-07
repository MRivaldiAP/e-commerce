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
                          @endphp
                          <div class="mb-2">
                            <span class="badge {{ $shippingLabel }} text-capitalize">{{ str_replace('_', ' ', $shippingStatus) }}</span>
                          </div>
                          <div class="small text-muted">Kurir: {{ strtoupper($shippingData->courier ?? '-') }} {{ $shippingData?->service ? '(' . $shippingData->service . ')' : '' }}</div>
                          <div class="small text-muted">Nomor Resi: {{ $shippingData?->tracking_number ?? 'Belum diatur' }}</div>
                          <div class="small text-muted">ID Eksternal: {{ $shippingData?->external_id ?? 'Tidak ada' }}</div>
                          <div class="small text-muted">Ongkir: Rp {{ number_format($shippingData?->cost ?? 0, 0, ',', '.') }}</div>
                          @php
                            $activeOrderOld = (int) old('order_id', 0) === $order->id;
                          @endphp
                          <details class="mt-2">
                            <summary class="small font-weight-semibold text-primary" style="cursor: pointer;">Kelola Pengiriman</summary>
                            <div class="mt-2 p-2 bg-light rounded">
                              <form method="POST" action="{{ route('admin.orders.shipping.update', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <div class="form-group mb-2">
                                  <label class="small text-muted mb-1" for="tracking_number_{{ $order->id }}">Nomor Resi</label>
                                  <input type="text" class="form-control form-control-sm" id="tracking_number_{{ $order->id }}" name="tracking_number" value="{{ $activeOrderOld ? old('tracking_number') : $shippingData?->tracking_number }}" placeholder="Masukkan nomor resi">
                                </div>
                                <div class="form-group mb-2">
                                  <label class="small text-muted mb-1" for="external_id_{{ $order->id }}">ID Eksternal</label>
                                  <input type="text" class="form-control form-control-sm" id="external_id_{{ $order->id }}" name="external_id" value="{{ $activeOrderOld ? old('external_id') : $shippingData?->external_id }}" placeholder="ID dari gateway (opsional)">
                                </div>
                                <div class="form-group mb-2">
                                  <label class="small text-muted mb-1" for="courier_{{ $order->id }}">Kurir</label>
                                  <input type="text" class="form-control form-control-sm" id="courier_{{ $order->id }}" name="courier" value="{{ $activeOrderOld ? old('courier') : $shippingData?->courier }}" placeholder="Kode kurir">
                                </div>
                                <div class="form-group mb-2">
                                  <label class="small text-muted mb-1" for="service_{{ $order->id }}">Layanan</label>
                                  <input type="text" class="form-control form-control-sm" id="service_{{ $order->id }}" name="service" value="{{ $activeOrderOld ? old('service') : $shippingData?->service }}" placeholder="Nama layanan">
                                </div>
                                <div class="form-group mb-2">
                                  <label class="small text-muted mb-1" for="status_{{ $order->id }}">Status</label>
                                  <select id="status_{{ $order->id }}" class="form-control form-control-sm" name="status">
                                    <option value="">-- Pilih status --</option>
                                    @foreach(['pending' => 'Menunggu', 'packing' => 'Sedang diproses', 'in_transit' => 'Sedang dikirim', 'delivered' => 'Terkirim', 'cancelled' => 'Dibatalkan'] as $value => $label)
                                      <option value="{{ $value }}" {{ ($activeOrderOld ? old('status') : $shippingStatus) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="form-group mb-2">
                                  <label class="small text-muted mb-1" for="cost_{{ $order->id }}">Biaya Kirim</label>
                                  <input type="number" class="form-control form-control-sm" id="cost_{{ $order->id }}" name="cost" value="{{ $activeOrderOld ? old('cost') : $shippingData?->cost }}" min="0" step="0.01">
                                </div>
                                <div class="form-group mb-3">
                                  <label class="small text-muted mb-1" for="estimated_delivery_{{ $order->id }}">Estimasi Tiba</label>
                                  <input type="date" class="form-control form-control-sm" id="estimated_delivery_{{ $order->id }}" name="estimated_delivery" value="{{ $activeOrderOld ? old('estimated_delivery') : optional($shippingData?->estimated_delivery)->format('Y-m-d') }}">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary btn-block">Simpan Perubahan</button>
                              </form>

                              <div class="d-flex flex-wrap mt-3" style="gap: .5rem;">
                                <form method="POST" action="{{ route('admin.orders.shipping.create', $order) }}">
                                  @csrf
                                  <button type="submit" class="btn btn-sm btn-outline-primary">Buat Pengiriman</button>
                                </form>
                                <form method="POST" action="{{ route('admin.orders.shipping.track', $order) }}">
                                  @csrf
                                  <input type="hidden" name="tracking_number" value="{{ $shippingData?->tracking_number }}">
                                  <input type="hidden" name="courier" value="{{ $shippingData?->courier }}">
                                  <button type="submit" class="btn btn-sm btn-outline-info" {{ empty($shippingData?->tracking_number) ? 'disabled' : '' }}>Lacak</button>
                                </form>
                                <form method="POST" action="{{ route('admin.orders.shipping.cancel', $order) }}">
                                  @csrf
                                  <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                                </form>
                              </div>
                            </div>
                          </details>
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
