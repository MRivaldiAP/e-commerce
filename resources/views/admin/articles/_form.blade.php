@php
  $isEdit = isset($article);
@endphp

<div class="row">
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-body">
        <h4 class="card-title">Konten Artikel</h4>
        <div class="form-group">
          <label for="title">Judul<span class="text-danger">*</span></label>
          <input type="text" id="title" name="title" value="{{ old('title', $article->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Judul artikel yang menarik">
          @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="slug">Slug URL</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">{{ url('/artikel') }}/</span>
            </div>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $article->slug ?? '') }}" class="form-control @error('slug') is-invalid @enderror" placeholder="slug-artikel-yang-seo-friendly">
          </div>
          <small class="form-text text-muted">Biarkan kosong untuk membuat slug otomatis dari judul.</small>
          @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="excerpt">Ringkasan</label>
          <textarea id="excerpt" name="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Tulis ringkasan singkat untuk ditampilkan di daftar artikel.">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
          <small class="form-text text-muted">Ringkasan membantu pembaca memahami isi artikel dan digunakan sebagai fallback meta description.</small>
          @error('excerpt')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group mb-0">
          <label for="content">Konten Lengkap<span class="text-danger">*</span></label>
          <textarea id="content" name="content" rows="18" class="form-control form-control-lg font-monospace @error('content') is-invalid @enderror" placeholder="Tulis konten artikel. Anda dapat menggunakan elemen HTML seperti &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, atau menyematkan gambar.">{{ old('content', $article->content ?? '') }}</textarea>
          <small class="form-text text-muted">Editor ini mendukung HTML sehingga Anda dapat membuat struktur konten yang kaya.</small>
          @error('content')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-body">
        <h4 class="card-title">Bantuan AI</h4>
        <p class="card-description">Masukkan kata kunci utama lalu klik tombol di bawah untuk mengisi seluruh kolom artikel secara otomatis dengan rekomendasi SEO terbaik.</p>
        <div class="form-group">
          <label for="ai_keywords">Kata Kunci Target</label>
          <input type="text" id="ai_keywords" name="ai_keywords" value="{{ old('ai_keywords') }}" class="form-control" placeholder="contoh: suplemen herbal untuk stamina, vitamin alami harian">
          <small class="form-text text-muted">Pisahkan beberapa kata kunci dengan koma untuk memberikan konteks tambahan.</small>
        </div>
        <button type="button" class="btn btn-primary btn-block" id="generate-with-ai">
          <i class="mdi mdi-robot"></i>
          <span class="ml-1">Buat dengan AI</span>
        </button>
        <div id="ai-status" class="alert d-none mt-3" role="alert"></div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <h4 class="card-title">Meta SEO</h4>
        <div class="form-group">
          <label for="meta_title">Meta Title</label>
          <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $article->meta_title ?? '') }}" class="form-control @error('meta_title') is-invalid @enderror" placeholder="Judul SEO (maks 60 karakter)">
          <small class="form-text text-muted">Jika dikosongkan, judul artikel akan digunakan.</small>
          @error('meta_title')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label for="meta_description">Meta Description</label>
          <textarea id="meta_description" name="meta_description" rows="4" class="form-control @error('meta_description') is-invalid @enderror" placeholder="Deskripsi meta (ideal 150-160 karakter)">{{ old('meta_description', $article->meta_description ?? '') }}</textarea>
          <small class="form-text text-muted">Gunakan kalimat yang menggugah untuk meningkatkan CTR di hasil pencarian.</small>
          @error('meta_description')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <h4 class="card-title">Publikasi</h4>
        <div class="form-group">
          <div class="form-check form-check-flat form-check-primary">
            <label class="form-check-label">
              <input type="hidden" name="is_published" value="0">
              <input type="checkbox" class="form-check-input" name="is_published" value="1" {{ old('is_published', $article->is_published ?? true) ? 'checked' : '' }}>
              Terbitkan artikel segera
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="published_at">Tanggal Terbit</label>
          <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', optional($article->published_at ?? now())->format('Y-m-d\TH:i')) }}" class="form-control @error('published_at') is-invalid @enderror">
          <small class="form-text text-muted">Sesuaikan jadwal publikasi untuk mengatur kronologi konten.</small>
          @error('published_at')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Gambar Unggulan</h4>
        @if(($article->featured_image ?? false))
          <div class="mb-3 text-center">
            <img src="{{ asset('storage/' . $article->featured_image) }}" alt="Featured image" class="img-fluid rounded shadow-sm">
          </div>
        @endif
        <div class="form-group">
          <input type="file" name="featured_image" accept="image/*" class="form-control-file @error('featured_image') is-invalid @enderror">
          <small class="form-text text-muted">Gunakan ukuran 1200x630px untuk tampilan terbaik di media sosial.</small>
          @error('featured_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
        @if(($article->featured_image ?? false))
          <div class="form-group">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="remove_featured_image" name="remove_featured_image" value="1">
              <label class="form-check-label" for="remove_featured_image">Hapus gambar unggulan</label>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
