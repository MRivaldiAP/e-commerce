@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Artikel</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">SEO</a></li>
          <li class="breadcrumb-item active" aria-current="page">Artikel</li>
        </ol>
      </nav>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
              <div>
                <h4 class="card-title mb-1">Daftar Artikel</h4>
                <small class="text-muted">Kelola konten artikel yang SEO friendly untuk website Anda.</small>
              </div>
              <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus-circle-outline mr-1"></i> Artikel Baru
              </a>
            </div>

            <form method="GET" class="mb-4">
              <div class="form-row row gx-2 gy-2">
                <div class="col-md-4">
                  <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari judul atau metadata...">
                </div>
                <div class="col-md-3">
                  <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Terbit</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select name="per_page" class="form-control">
                    @foreach([10,20,30,50] as $size)
                      <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }} per halaman</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search','status','per_page']))
                      <a href="{{ route('admin.articles.index') }}" class="btn btn-inverse-secondary">Reset</a>
                    @endif
                  </div>
                </div>
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Judul</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Dipublikasikan</th>
                    <th>Meta Title</th>
                    <th style="width:140px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($articles as $article)
                    <tr>
                      <td>
                        <strong>{{ $article->title }}</strong>
                        @if($article->excerpt)
                          <div class="text-muted small">{{ Str::limit(strip_tags($article->excerpt), 90) }}</div>
                        @endif
                      </td>
                      <td>
                        <span class="badge badge-outline-primary">{{ $article->slug }}</span>
                      </td>
                      <td>
                        @if($article->is_published)
                          <span class="badge badge-success">Terbit</span>
                        @else
                          <span class="badge badge-secondary">Draft</span>
                        @endif
                      </td>
                      <td>
                        {{ optional($article->published_at)->format('d M Y H:i') ?? '—' }}
                      </td>
                      <td>
                        {{ $article->meta_title ?? '—' }}
                        @if($article->meta_description)
                          <div class="text-muted small">{{ Str::limit($article->meta_description, 90) }}</div>
                        @endif
                      </td>
                      <td>
                        <div class="btn-group btn-group-sm" role="group">
                          <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-outline-primary">Edit</a>
                          <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Hapus</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center py-4">
                        <div class="py-4">
                          <h5 class="text-muted mb-2">Belum ada artikel</h5>
                          <p class="mb-3">Mulai buat artikel pertama Anda untuk menarik trafik organik dan pelanggan baru.</p>
                          <a href="{{ route('admin.articles.create') }}" class="btn btn-sm btn-primary">Tulis Artikel</a>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="mt-4">
              {{ $articles->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
