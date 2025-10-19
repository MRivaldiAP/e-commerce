@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Ubah Item Galeri</h3>
      <a href="{{ route('admin.gallery.items.index') }}" class="btn btn-light">Kembali</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.gallery.items.update', $item) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="title">Judul</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $item->title) }}" required>
            @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="gallery_category_id">Kategori</label>
            <select class="form-control @error('gallery_category_id') is-invalid @enderror" id="gallery_category_id" name="gallery_category_id">
              <option value="">Tanpa Kategori</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('gallery_category_id', $item->gallery_category_id) == $category->id)>{{ $category->name }}</option>
              @endforeach
            </select>
            @error('gallery_category_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $item->description) }}</textarea>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="position">Urutan</label>
            <input type="number" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', $item->position) }}" min="0">
            @error('position')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label>Gambar Saat Ini</label>
            <div class="mb-2">
              <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" style="width:160px;height:120px;object-fit:cover;border-radius:6px;">
            </div>
            <label for="image">Ganti Gambar (opsional)</label>
            <input type="file" class="form-control-file @error('image') is-invalid @enderror" id="image" name="image">
            @error('image')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
