@extends('layout.admin')
@section('content')
<style>
  .tooltip-inner {
    max-width: none;
    padding: 0;
  }
</style>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Produk</h3> <!-- Judul halaman -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Katalog</a></li>
          <li class="breadcrumb-item active" aria-current="page">Produk</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="card-title mb-0">Daftar Produk</h4>
                <small class="text-muted">Kelola produk Anda, filter, dan aksi massal</small>
              </div>

              <div class="d-flex gap-2">
                <!-- Tombol tambah produk -->
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Tambah Produk</a>
                <!-- Tombol export -->
                <a href="{{ route('admin.products.index') }}?export=csv" class="btn btn-outline-secondary btn-sm">Export CSV</a>
                <!-- Form aksi massal (hapus banyak produk sekaligus) -->
                <form id="bulk-action-form" method="POST" action="{{ route('admin.products.bulk') }}" style="display:inline">
                  @csrf
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="ids" id="bulk-ids" value="">
                  <button type="button" id="bulk-delete-btn" class="btn btn-danger btn-sm">Hapus Terpilih</button>
                </form>
              </div>
            </div>

            <!-- Form filter pencarian -->
            <form method="GET" action="{{ route('admin.products.index') }}" class="mb-3">
              <div class="form-row row gx-2">
                <!-- Input keyword -->
                <div class="col-md-4 mb-2">
                  <input type="text" name="keyword" class="form-control" placeholder="Cari nama, SKU..." value="{{ old('keyword', request('keyword', $filters['keyword'] ?? '')) }}">
                </div>

                <!-- Filter kategori -->
                <div class="col-md-3 mb-2">
                  <select name="category_id" class="form-control">
                    <option value="">Semua Kategori</option>
                    @if(isset($categories))
                      @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ (string)request('category_id') === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>

                <!-- Filter status -->
                <div class="col-md-2 mb-2">
                  <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status')==='1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status')==='0' ? 'selected' : '' }}>Nonaktif</option>
                  </select>
                </div>

                <!-- Pilihan jumlah data per halaman -->
                <div class="col-md-2 mb-2">
                  <select name="per_page" class="form-control">
                    <option value="10" {{ request('per_page', 15)==10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page', 15)==15 ? 'selected' : '' }}>15</option>
                    <option value="30" {{ request('per_page', 15)==30 ? 'selected' : '' }}>30</option>
                    <option value="50" {{ request('per_page', 15)==50 ? 'selected' : '' }}>50</option>
                  </select>
                </div>

                <!-- Tombol submit filter -->
                <div class="col-md-1 mb-2">
                  <button class="btn btn-primary w-100">Filter</button>
                </div>
              </div>
            </form>

            <!-- Tabel produk -->
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th style="width:40px"><input type="checkbox" id="select-all"></th> <!-- Checkbox pilih semua -->
                    <th>Gambar</th>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th style="width:130px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($products as $product)
                    <tr>
                      <td><input type="checkbox" class="row-checkbox" value="{{ $product->id }}"></td>

                      <td>
                        @php
                          $images = $product->images;
                          $thumb  = optional($images->first())->path;
                          $tooltip = '<div style="display:flex">' .
                            $images->map(fn($img) => "<img src='" . asset('storage/' . $img->path) . "' style='width:70px;height:50px;object-fit:cover;margin-right:4px;'>")->implode('') .
                            '</div>';
                        @endphp
                        @if($thumb)
                          <!-- Jika produk punya gambar -->
                          <img src="{{ asset('storage/' . $thumb) }}" alt="thumb" style="width:60px;height:45px;object-fit:cover;border-radius:4px" data-toggle="tooltip" data-html="true" title="{!! $tooltip !!}">
                        @else
                          <!-- Jika tidak ada gambar -->
                          <div style="width:60px;height:45px;background:#f1f1f1;display:flex;align-items:center;justify-content:center;color:#aaa;border-radius:4px">-</div>
                        @endif
                      </td>

                      <td>{{ $product->sku ?? '-' }}</td>

                      <td>
                        <strong>{{ $product->name }}</strong>
                        @if(!empty($product->short_description))
                          <!-- Tampilkan deskripsi singkat kalau ada -->
                          <div class="text-muted small">{{ Str::limit($product->short_description, 60) }}</div>
                        @endif
                      </td>

                      <td>Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</td>

                      <td>{{ $product->stock ?? 0 }}</td>

                      <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $product->categories->pluck('name')->join(', ') ?: '-' }}
                      </td>

                      <td>
                        @if($product->status)
                          <label class="badge badge-success">Aktif</label>
                        @else
                          <label class="badge badge-secondary">Nonaktif</label>
                        @endif
                      </td>

                      <td>{{ $product->created_at?->format('Y-m-d') }}</td>

                      <td>
                        <div class="d-flex flex-nowrap overflow-auto" style="gap:4px;">
                          <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat"><i class="mdi mdi-eye"></i></a>
                          <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>
                          <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus produk ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Hapus"><i class="mdi mdi-delete"></i></button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center">Produk tidak ditemukan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Informasi pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
              <div>
                <small class="text-muted">Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() ?? 0 }}</small>
              </div>
              <div>
                {{ $products->appends(request()->query())->links() }}
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@section('script')
<script>
  $(function(){
    $('[data-toggle="tooltip"]').tooltip({html:true});
  });

  document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all'); // Checkbox pilih semua
    const checkboxes = document.querySelectorAll('.row-checkbox'); // Checkbox tiap baris
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn'); // Tombol hapus massal
    const bulkIdsInput = document.getElementById('bulk-ids'); // Hidden input untuk simpan ID

    // Event pilih semua checkbox
    if (selectAll) {
      selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
      });
    }

    // Event klik tombol hapus massal
    bulkDeleteBtn?.addEventListener('click', function () {
      const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
      if (!selected.length) {
        return alert('Pilih minimal satu produk.');
      }

      if (!confirm('Yakin ingin menghapus produk yang dipilih? Aksi ini tidak bisa dibatalkan.')) return;

      // Simpan ID produk terpilih dalam input hidden lalu submit form
      bulkIdsInput.value = JSON.stringify(selected);
      document.getElementById('bulk-action-form').submit();
    });
  });
</script>
@endsection

@endsection

