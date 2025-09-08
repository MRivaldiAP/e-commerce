@extends('layout.admin')

@section('content')
<style>
  .product-thumbnail { cursor: pointer; }
</style>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Detail Produk</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Katalog</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produk</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <img id="main-product-image" src="{{ $product->images->first() ? asset('storage/'.$product->images->first()->path) : 'https://via.placeholder.com/400x300?text=No+Image' }}" class="w-100 border" style="object-fit:cover;" alt="Gambar Produk">
                </div>
                @if($product->images->count() > 1)
                  <div class="d-flex flex-wrap" style="gap:8px;">
                    @foreach($product->images as $img)
                      <img src="{{ asset('storage/'.$img->path) }}" data-full="{{ asset('storage/'.$img->path) }}" class="product-thumbnail border rounded" style="width:80px;height:80px;object-fit:cover;" alt="">
                    @endforeach
                  </div>
                @endif
              </div>

              <div class="col-md-6">
                <table class="table table-sm">
                  <tr><th>Nama</th><td>{{ $product->name }}</td></tr>
                  <tr><th>SKU</th><td>{{ $product->sku ?: '-' }}</td></tr>
                  <tr><th>Harga</th><td>Rp {{ number_format($product->price,0,',','.') }}</td></tr>
                  @if($product->sale_price)
                    <tr><th>Harga Diskon</th><td>Rp {{ number_format($product->sale_price,0,',','.') }}</td></tr>
                  @endif
                  <tr><th>Stok</th><td>{{ $product->stock }}</td></tr>
                  <tr><th>Kategori</th><td>{{ $product->categories->pluck('name')->join(', ') ?: '-' }}</td></tr>
                  <tr><th>Merek</th><td>{{ $product->brand?->name ?: '-' }}</td></tr>
                  <tr><th>Status</th><td>{{ $product->status ? 'Aktif' : 'Nonaktif' }}</td></tr>
                  <tr><th>Dimensi (L x W x H)</th><td>{{ $product->length }} x {{ $product->width }} x {{ $product->height }}</td></tr>
                  <tr><th>Berat</th><td>{{ $product->weight ? $product->weight.' kg' : '-' }}</td></tr>
                  @if($product->tags)
                    <tr><th>Tag</th><td>{{ $product->tags }}</td></tr>
                  @endif
                </table>
              </div>
            </div>

            <div class="mt-4">
              <h4>Deskripsi</h4>
              <p id="product-description" style="max-height:120px;overflow:hidden;">{!! nl2br(e($product->description)) !!}</p>
              @if($product->description && strlen($product->description) > 200)
                <button class="btn btn-sm btn-outline-primary" id="toggle-description">Tampilkan semua</button>
              @endif
            </div>

            <div class="mt-4">
              <h4>Meta</h4>
              <p><strong>Meta Title:</strong> {{ $product->meta_title ?: '-' }}</p>
              <p><strong>Meta Description:</strong> <span id="meta-desc">{{ Str::limit($product->meta_description, 150) }}</span></p>
              @if($product->meta_description && strlen($product->meta_description) > 150)
                <button class="btn btn-sm btn-outline-secondary" id="toggle-meta">Tampilkan semua</button>
              @endif
            </div>

            <div class="mt-4">
              <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">Edit Produk</a>
              <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Kembali</a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Ganti gambar utama saat thumbnail diklik
    const mainImage = document.getElementById('main-product-image');
    document.querySelectorAll('.product-thumbnail').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        mainImage.src = this.dataset.full;
      });
    });

    // Toggle deskripsi panjang
    const desc = document.getElementById('product-description');
    const toggleDesc = document.getElementById('toggle-description');
    if (toggleDesc) {
      toggleDesc.addEventListener('click', function () {
        if (desc.style.maxHeight && desc.style.maxHeight !== 'none') {
          desc.style.maxHeight = 'none';
          this.textContent = 'Sembunyikan';
        } else {
          desc.style.maxHeight = '120px';
          this.textContent = 'Tampilkan semua';
        }
      });
    }

    // Toggle meta description
    const metaDesc = document.getElementById('meta-desc');
    const toggleMeta = document.getElementById('toggle-meta');
    if (toggleMeta) {
      const full = metaDesc.textContent;
      const short = full.substring(0, 150) + (full.length > 150 ? '...' : '');
      let expanded = false;
      toggleMeta.addEventListener('click', function () {
        expanded = !expanded;
        metaDesc.textContent = expanded ? full : short;
        this.textContent = expanded ? 'Sembunyikan' : 'Tampilkan semua';
      });
    }
  });
</script>
@endsection
