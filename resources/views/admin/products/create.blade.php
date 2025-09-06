@extends('layout.admin')

@section('content')
<!-- resources/views/products/create.blade.php -->
<!--
  Catatan:
  - Ini adalah tampilan "Tambah Produk" menggunakan template halaman yang sudah disediakan.
  - Sesuaikan route name (products.store) dengan routing controller/service kamu.
  - Controller harus mengirimkan: $categories (id,name), $brands (id,name) -- sesuaikan nama jika berbeda.
  - Controller yang menangani POST harus memanggil fungsi Product service (misalnya ProductService::createFromRequest atau sejenisnya) dan menangani upload gambar.
-->

<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Formulir</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Formulir</a></li>
          <li class="breadcrumb-item active" aria-current="page"> Formulir </li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Tambah Produk</h4>

            <!-- Ganti action agar sesuai dengan route yang memanggil service produk -->
            <form class="form-sample" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
              @csrf

              <p class="card-description">Informasi Dasar</p>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nama Produk</label>
                    <div class="col-sm-9">
                      <input type="text" name="name" id="product-name" class="form-control" value="{{ old('name') }}" required />
                      @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">SKU</label>
                    <div class="col-sm-9">
                      <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" />
                      @error('sku') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Slug</label>
                    <div class="col-sm-9">
                      <input type="text" name="slug" id="product-slug" class="form-control" value="{{ old('slug') }}" />
                      <small class="form-text text-muted">Otomatis dari nama, bisa diedit.</small>
                      @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Kategori</label>
                    <div class="col-sm-9">
                      <select name="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories ?? [] as $cat)
                          <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                      </select>
                      @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Merek</label>
                    <div class="col-sm-9">
                      <select name="brand_id" class="form-control">
                        <option value="">-- Pilih Merek --</option>
                        @foreach($brands ?? [] as $brand)
                          <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                      </select>
                      @error('brand_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Status</label>
                    <div class="col-sm-9">
                      <select name="status" class="form-control">
                        <option value="1" {{ old('status',1) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Draft</option>
                      </select>
                      @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <p class="card-description">Harga & Stok</p>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Harga</label>
                    <div class="col-sm-9">
                      <input type="number" name="price" step="0.01" class="form-control" value="{{ old('price') }}" required />
                      @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Harga Diskon</label>
                    <div class="col-sm-9">
                      <input type="number" name="sale_price" step="0.01" class="form-control" value="{{ old('sale_price') }}" />
                      @error('sale_price') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Stok</label>
                    <div class="col-sm-9">
                      <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" />
                      @error('stock') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Berat (gram)</label>
                    <div class="col-sm-9">
                      <input type="number" name="weight" step="0.01" class="form-control" value="{{ old('weight') }}" />
                      @error('weight') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <p class="card-description">Dimensi (opsional)</p>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Panjang (cm)</label>
                    <div class="col-sm-8">
                      <input type="number" name="length" step="0.01" class="form-control" value="{{ old('length') }}" />
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Lebar (cm)</label>
                    <div class="col-sm-8">
                      <input type="number" name="width" step="0.01" class="form-control" value="{{ old('width') }}" />
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tinggi (cm)</label>
                    <div class="col-sm-8">
                      <input type="number" name="height" step="0.01" class="form-control" value="{{ old('height') }}" />
                    </div>
                  </div>
                </div>
              </div>

              <p class="card-description">Gambar</p>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Gambar Utama</label>
                    <div class="col-sm-9">
                      <input type="file" name="image" accept="image/*" class="form-control-file" />
                      @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Galeri</label>
                    <div class="col-sm-9">
                      <input type="file" name="images[]" id="images-input" accept="image/*" multiple class="form-control-file" />
                      <small class="form-text text-muted">Bisa unggah banyak gambar. Maksimal disarankan 10.</small>
                      @error('images') <small class="text-danger">{{ $message }}</small> @enderror

                      <div id="images-preview" class="mt-2 d-flex flex-wrap"></div>
                    </div>
                  </div>
                </div>
              </div>

              <p class="card-description">Deskripsi & SEO</p>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Deskripsi Singkat</label>
                    <div class="col-sm-10">
                      <textarea name="short_description" rows="3" class="form-control">{{ old('short_description') }}</textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Deskripsi</label>
                    <div class="col-sm-10">
                      <textarea name="description" rows="6" class="form-control">{{ old('description') }}</textarea>
                      @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Judul Meta</label>
                    <div class="col-sm-9">
                      <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}" />
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Deskripsi Meta</label>
                    <div class="col-sm-9">
                      <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description') }}" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Tag</label>
                    <div class="col-sm-9">
                      <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="pisahkan dengan koma" />
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Produk Unggulan</label>
                    <div class="col-sm-9">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input type="checkbox" class="form-check-input" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}> Ya
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12 text-right">
                  <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
                  <button type="submit" class="btn btn-primary">Simpan Produk</button>
                </div>
              </div>

            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Script kecil untuk slug otomatis & preview gambar -->
<script>
  (function(){
    // slug otomatis dari nama
    const nameInput = document.getElementById('product-name');
    const slugInput = document.getElementById('product-slug');
    if(nameInput && slugInput){
      nameInput.addEventListener('input', function(e){
        const val = e.target.value.trim().toLowerCase();
        const slug = val.replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
        slugInput.value = slug;
      });
    }

    // preview gambar
    const imagesInput = document.getElementById('images-input');
    const imagesPreview = document.getElementById('images-preview');
    function clearChildren(el){ while(el.firstChild) el.removeChild(el.firstChild); }

    if(imagesInput && imagesPreview){
      imagesInput.addEventListener('change', function(){
        clearChildren(imagesPreview);
        const files = Array.from(this.files).slice(0, 10); // batasi preview 10
        files.forEach(file => {
          if(!file.type.startsWith('image/')) return;
          const reader = new FileReader();
          const wrapper = document.createElement('div');
          wrapper.style.width = '80px';
          wrapper.style.height = '80px';
          wrapper.style.marginRight = '8px';
          wrapper.style.marginBottom = '8px';
          wrapper.style.position = 'relative';

          reader.onload = function(ev){
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.border = '1px solid #ddd';
            img.style.borderRadius = '4px';

            wrapper.appendChild(img);
            imagesPreview.appendChild(wrapper);
          }
          reader.readAsDataURL(file);
        });
      });
    }
  })();
</script>

@endsection