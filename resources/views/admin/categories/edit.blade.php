@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Kategori</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Katalog</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Kategori</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Edit Kategori</h4>
            <form class="form-sample" action="{{ route('admin.categories.update', $category->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nama</label>
                    <div class="col-sm-9">
                      <input type="text" name="name" id="category-name" class="form-control" value="{{ old('name', $category->name) }}" required />
                      @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Slug</label>
                    <div class="col-sm-9">
                      <input type="text" name="slug" id="category-slug" class="form-control" value="{{ old('slug', $category->slug) }}" />
                      <small class="form-text text-muted">Otomatis dari nama, bisa diedit.</small>
                      @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Deskripsi</label>
                    <div class="col-sm-10">
                      <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                      @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-md-12 text-right">
                  <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
                  <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  (function() {
    const nameInput = document.getElementById('category-name');
    const slugInput = document.getElementById('category-slug');
    if (nameInput && slugInput) {
      nameInput.addEventListener('input', function(e) {
        const val = e.target.value.trim().toLowerCase();
        const slug = val.replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        slugInput.value = slug;
      });
    }
  })();
</script>
@endsection
