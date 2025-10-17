@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Tambah Item Galeri</h3>
      <a href="{{ route('admin.gallery.items.index') }}" class="btn btn-light">Kembali</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.gallery.items.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label for="title">Judul</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
            @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="gallery_category_id">Kategori</label>
            <select class="form-control @error('gallery_category_id') is-invalid @enderror" id="gallery_category_id" name="gallery_category_id">
              <option value="">Tanpa Kategori</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('gallery_category_id') == $category->id)>{{ $category->name }}</option>
              @endforeach
            </select>
            @error('gallery_category_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="position">Urutan</label>
            <input type="number" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', 0) }}" min="0">
            @error('position')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="image">Gambar</label>
            <input type="file" class="form-control-file @error('image') is-invalid @enderror" id="image" name="image" required>
            @error('image')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
