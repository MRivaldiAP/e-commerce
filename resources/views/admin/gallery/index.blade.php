@extends('layout.admin')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $imageUrl = function (?string $path): string {
        if (empty($path)) {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage', 'storage/'])) {
            $normalized = ltrim($path, '/');

            return asset($normalized);
        }

        return Storage::url($path);
    };
@endphp

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Galeri</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Konten</a></li>
          <li class="breadcrumb-item active" aria-current="page">Galeri</li>
        </ol>
      </nav>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row">
      <div class="col-lg-5 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Tambah Kategori</h4>
            <p class="card-description">Kategori digunakan sebagai filter pada halaman galeri.</p>
            <form action="{{ route('admin.gallery.categories.store') }}" method="POST">
              @csrf
              <div class="form-group">
                <label for="category-name">Nama</label>
                <input type="text" class="form-control" id="category-name" name="name" value="{{ old('name') }}" required>
              </div>
              <div class="form-group">
                <label for="category-slug">Slug</label>
                <input type="text" class="form-control" id="category-slug" name="slug" value="{{ old('slug') }}" required>
                <small class="form-text text-muted">Gunakan huruf kecil tanpa spasi, misal: <code>pernikahan</code>.</small>
              </div>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-7 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Daftar Kategori</h4>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th style="width: 120px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($categories as $category)
                    <tr>
                      <td>{{ $category['name'] ?? '-' }}</td>
                      <td>{{ $category['slug'] ?? '-' }}</td>
                      <td>
                        <form action="{{ route('admin.gallery.categories.destroy', $category['slug']) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini? Item pada kategori tersebut juga akan dihapus.');">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-danger" type="submit"><i class="mdi mdi-delete"></i> Hapus</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-center">Belum ada kategori galeri.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-5 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Tambah Foto</h4>
            <p class="card-description">Unggah foto galeri dan hubungkan ke kategori.</p>
            @php $hasCategories = $categories->isNotEmpty(); @endphp
            @if(!$hasCategories)
              <div class="alert alert-warning">Tambahkan kategori terlebih dahulu sebelum mengunggah foto.</div>
            @endif
            <form action="{{ route('admin.gallery.items.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="form-group">
                <label for="item-title">Judul</label>
                <input type="text" class="form-control" id="item-title" name="title" value="{{ old('title') }}" required>
              </div>
              <div class="form-group">
                <label for="item-category">Kategori</label>
                <select class="form-control" id="item-category" name="category" required {{ $hasCategories ? '' : 'disabled' }}>
                  <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih kategori</option>
                  @foreach($categories as $category)
                    <option value="{{ $category['slug'] }}" {{ old('category') === ($category['slug'] ?? '') ? 'selected' : '' }}>{{ $category['name'] ?? $category['slug'] }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label for="item-description">Deskripsi</label>
                <textarea class="form-control" id="item-description" name="description" rows="3">{{ old('description') }}</textarea>
              </div>
              <div class="form-group">
                <label for="item-image">Foto</label>
                <input type="file" class="form-control-file" id="item-image" name="image" accept="image/*" required>
                <small class="form-text text-muted">Format JPG, PNG, atau WebP dengan ukuran maksimal 5MB.</small>
              </div>
              <button type="submit" class="btn btn-primary" {{ $hasCategories ? '' : 'disabled' }}>Unggah</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-7 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="card-title mb-0">Item Galeri</h4>
                <small class="text-muted">Total {{ $items->count() }} foto.</small>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>Foto</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th style="width: 120px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($items as $item)
                    @php
                      $url = $imageUrl($item['image'] ?? null);
                      $categoryData = $categoryMap->get($item['category'] ?? '');
                      $categoryName = $categoryData['name'] ?? ($item['category'] ?? '-');
                    @endphp
                    <tr>
                      <td style="width: 120px;">
                        @if($url)
                          <img src="{{ $url }}" alt="{{ $item['title'] ?? 'Galeri' }}" class="img-fluid rounded" style="max-height:80px; object-fit:cover;">
                        @else
                          <span class="text-muted">Tidak ada gambar</span>
                        @endif
                      </td>
                      <td>
                        <strong>{{ $item['title'] ?? '-' }}</strong>
                        @if(!empty($item['description']))
                          <div class="text-muted small">{{ Str::limit($item['description'], 80) }}</div>
                        @endif
                      </td>
                      <td>{{ $categoryName }}</td>
                      <td>
                        <form action="{{ route('admin.gallery.items.destroy', $item['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus foto ini dari galeri?');">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-danger" type="submit"><i class="mdi mdi-delete"></i> Hapus</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center">Belum ada foto galeri.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
