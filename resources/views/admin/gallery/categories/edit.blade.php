@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Ubah Kategori Galeri</h3>
      <a href="{{ route('admin.gallery.categories.index') }}" class="btn btn-light">Kembali</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.gallery.categories.update', $category) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $category->slug) }}">
            @error('slug')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Jika dikosongkan slug akan dibuat otomatis dari nama.</small>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
