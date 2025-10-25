@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header d-flex align-items-center justify-content-between">
      <div>
        <h3 class="page-title">Promo Produk</h3>
        <p class="text-muted mb-0">Kelola promo diskon untuk produk Anda dan atur periode berlakunya.</p>
      </div>
      <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary btn-icon-text">
        <i class="mdi mdi-plus btn-icon-prepend"></i> Tambah Promo
      </a>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Daftar Promo</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Periode</th>
                    <th>Produk</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($promotions as $promotion)
                    @php
                      $isActive = $promotion->isActive();
                      $startsAt = $promotion->starts_at ? $promotion->starts_at->format('d M Y H:i') : 'Segera';
                      $endsAt = $promotion->ends_at ? $promotion->ends_at->format('d M Y H:i') : 'Tanpa Batas';
                    @endphp
                    <tr>
                      <td>
                        <strong>{{ $promotion->name }}</strong>
                        <div class="text-muted small">{{ $promotion->label }}</div>
                      </td>
                      <td>
                        @if($promotion->discount_type === \App\Models\Promotion::TYPE_PERCENTAGE)
                          Persentase ({{ rtrim(rtrim(number_format($promotion->discount_value, 2, ',', '.'), '0'), ',') }}%)
                        @else
                          Potongan Rp {{ number_format($promotion->discount_value, 0, ',', '.') }}
                        @endif
                      </td>
                      <td>
                        <div>{{ $startsAt }}</div>
                        <div class="text-muted small">s/d {{ $endsAt }}</div>
                      </td>
                      <td>{{ $promotion->products_count }}</td>
                      <td>
                        <span class="badge badge-{{ $isActive ? 'success' : 'secondary' }}">{{ $isActive ? 'Aktif' : 'Tidak Aktif' }}</span>
                      </td>
                      <td class="text-right">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus promo ini?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted">Belum ada promo yang dibuat.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div class="mt-3">
              {{ $promotions->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
