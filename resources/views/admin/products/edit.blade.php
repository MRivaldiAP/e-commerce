@extends('layout.admin')

@section('content')
<!-- resources/views/products/edit.blade.php -->
<!--
  Catatan:
  - Tampilan "Edit Produk" memanfaatkan template halaman yang sudah ada.
  - Route update: admin.products.update
  - Controller harus mengirimkan: $product, $categories (id,name), $brands (id,name)
  - Tinjau kembali validasi/logic di controller & service sesuai kebutuhan Anda.
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
            <h4 class="card-title">Edit Produk</h4>

            <!-- Ganti action agar sesuai dengan route update produk -->
            <form class="form-sample" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')

              <p class="card-description">Informasi Dasar</p>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nama Produk</label>
                    <div class="col-sm-9">
                      <input type="text" name="name" id="product-name" class="form-control" value="{{ old('name', $product->name) }}" required />
                      @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">SKU</label>
                    <div class="col-sm-9">
                      <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" />
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
                      <input type="text" name="slug" id="product-slug" class="form-control" value="{{ old('slug', $product->slug) }}" />
                      <small class="form-text text-muted">Otomatis dari nama, bisa diedit.</small>
                      @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Kategori</label>
                    <div class="col-sm-9">
                      @php
                        $selectedCategory = old('category_id', optional($product->categories->first())->id);
                      @endphp
                      <select name="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories ?? [] as $cat)
                          <option value="{{ $cat->id }}" {{ $selectedCategory == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                          <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
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
                        <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $product->status) == '0' ? 'selected' : '' }}>Draft</option>
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
                      <input type="number" name="price" step="0.01" class="form-control" value="{{ old('price', $product->price) }}" required />
                      @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Harga Diskon</label>
                    <div class="col-sm-9">
                      <input type="number" name="sale_price" step="0.01" class="form-control" value="{{ old('sale_price', $product->sale_price) }}" />
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
                      <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" />
                      @error('stock') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Berat (gram)</label>
                    <div class="col-sm-9">
                      <input type="number" name="weight" step="0.01" class="form-control" value="{{ old('weight', $product->weight) }}" />
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
                      <input type="number" name="length" step="0.01" class="form-control" value="{{ old('length', $product->length) }}" />
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Lebar (cm)</label>
                    <div class="col-sm-8">
                      <input type="number" name="width" step="0.01" class="form-control" value="{{ old('width', $product->width) }}" />
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tinggi (cm)</label>
                    <div class="col-sm-8">
                      <input type="number" name="height" step="0.01" class="form-control" value="{{ old('height', $product->height) }}" />
                    </div>
                  </div>
                </div>
              </div>

              <p class="card-description">Gambar</p>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Gambar Produk</label>
                    <div class="col-sm-10">
                      <input type="file" name="images[]" id="images-input" accept="image/*" multiple class="form-control-file" />
                      <small class="form-text text-muted">Bisa unggah banyak gambar. Maksimal disarankan 10.</small>
                      @error('images') <small class="text-danger">{{ $message }}</small> @enderror

                      <div class="d-flex flex-wrap mb-3">
                        @foreach($product->images as $img)
                          <div style="position:relative;margin-right:8px;margin-bottom:8px;width:80px;height:80px;">
                            <img src="{{ asset('storage/' . $img->path) }}" style="width:100%;height:100%;object-fit:cover;border:1px solid #ddd;border-radius:4px;" />
                            <button type="submit" form="delete-image-{{ $img->id }}" style="position:absolute;top:2px;right:2px;width:20px;height:20px;border:none;border-radius:50%;background:rgba(0,0,0,0.6);color:#fff;line-height:18px;cursor:pointer;">&times;</button>
                          </div>
                        @endforeach
                      </div>

                      <div id="images-preview" class="mt-2 d-flex flex-wrap"></div>
                    </div>
                  </div>
                </div>
              </div>

              <p class="card-description">Deskripsi & SEO</p>
              <p class="text-muted small mb-3">Isian di bawah membantu mesin pencari memahami produk Anda.</p>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Deskripsi Singkat</label>
                    <div class="col-sm-10">
                      <textarea name="short_description" rows="3" class="form-control">{{ old('short_description', $product->short_description) }}</textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Deskripsi</label>
                    <div class="col-sm-10">
                      <textarea name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea>
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
                      <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}" />
                      <small class="form-text text-muted">Judul singkat yang muncul di hasil pencarian. Contoh: "Sepatu Lari Pria Terbaik".</small>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Deskripsi Meta</label>
                    <div class="col-sm-9">
                      <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $product->meta_description) }}" />
                      <small class="form-text text-muted">Deskripsi singkat (maks. 160 karakter) untuk hasil pencarian. Contoh: "Sepatu lari ringan dan nyaman untuk segala medan."</small>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Tag</label>
                    <div class="col-sm-9">
                      <input type="text" name="tags" class="form-control" value="{{ old('tags', $product->tags) }}" placeholder="pisahkan dengan koma" />
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Produk Unggulan</label>
                    <div class="col-sm-9">
                      <div class="form-check">
                        <label class="form-check-label">
                          <input type="checkbox" class="form-check-input" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}> Ya
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
              <div class="col-md-12 text-right">
                  <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
                  <button type="submit" class="btn btn-primary">Perbarui Produk</button>
                </div>
              </div>

            </form>

            @foreach($product->images as $img)
              <form id="delete-image-{{ $img->id }}" action="{{ url('admin/products/'.$product->id.'/images/'.$img->id) }}" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
              </form>
            @endforeach

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

    // preview dan hapus gambar baru
    const imagesInput = document.getElementById('images-input');
    const imagesPreview = document.getElementById('images-preview');
    const dataTransfer = new DataTransfer();

    function clearChildren(el){ while(el.firstChild) el.removeChild(el.firstChild); }

    function refreshPreview(){
      clearChildren(imagesPreview);
      Array.from(dataTransfer.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(ev){
          const wrapper = document.createElement('div');
          wrapper.style.width = '80px';
          wrapper.style.height = '80px';
          wrapper.style.marginRight = '8px';
          wrapper.style.marginBottom = '8px';
          wrapper.style.position = 'relative';

          const img = document.createElement('img');
          img.src = ev.target.result;
          img.style.width = '100%';
          img.style.height = '100%';
          img.style.objectFit = 'cover';
          img.style.border = '1px solid #ddd';
          img.style.borderRadius = '4px';

          const removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.innerHTML = '&times;';
          removeBtn.style.position = 'absolute';
          removeBtn.style.top = '2px';
          removeBtn.style.right = '2px';
          removeBtn.style.width = '20px';
          removeBtn.style.height = '20px';
          removeBtn.style.border = 'none';
          removeBtn.style.borderRadius = '50%';
          removeBtn.style.background = 'rgba(0,0,0,0.6)';
          removeBtn.style.color = '#fff';
          removeBtn.style.lineHeight = '18px';
          removeBtn.style.cursor = 'pointer';
          removeBtn.addEventListener('click', function(){
            dataTransfer.items.remove(index);
            imagesInput.files = dataTransfer.files;
            refreshPreview();
          });

          wrapper.appendChild(img);
          wrapper.appendChild(removeBtn);
          imagesPreview.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
      });
    }

    if(imagesInput && imagesPreview){
      imagesInput.addEventListener('change', function(){
        Array.from(this.files).forEach(file => {
          if(!file.type.startsWith('image/')) return;
          if(dataTransfer.files.length >= 10) return; // batasi 10 gambar
          dataTransfer.items.add(file);
        });
        imagesInput.files = dataTransfer.files;
        refreshPreview();
      });
    }
  })();
</script>

@endsection
