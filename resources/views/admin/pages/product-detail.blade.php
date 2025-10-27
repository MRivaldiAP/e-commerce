@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      @include('admin.pages.partials.section-editor', [
        'sections' => $sections,
        'settings' => $settings,
        'availableSections' => $availableSections,
        'composition' => $composition,
        'defaultComposition' => $defaultComposition,
        'sectionLabels' => $sectionLabels,
        'updateRoute' => route('admin.pages.product-detail.update'),
      ])
      <div class="col-md-8 position-sticky" style="top:0;height:100vh">
        @if($previewUrl)
          <iframe id="page-preview" src="{{ $previewUrl }}" class="w-100 border h-100"></iframe>
        @else
          <div class="alert alert-info">Belum ada produk yang dapat ditampilkan. Tambahkan produk untuk melihat pratinjau.</div>
        @endif
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Kelola Komentar Produk</span>
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($comments->isEmpty())
              <p class="mb-0 text-muted">Belum ada komentar.</p>
            @else
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Produk</th>
                      <th>Pengguna</th>
                      <th>Komentar</th>
                      <th>Tanggal</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($comments as $comment)
                      <tr>
                        <td>{{ $comment->product?->name ?? '-' }}</td>
                        <td>{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($comment->content, 80) }}</td>
                        <td>{{ optional($comment->created_at)->format('d M Y H:i') }}</td>
                        <td>
                          @if($comment->is_active)
                            <span class="badge badge-success">Aktif</span>
                          @else
                            <span class="badge badge-secondary">Nonaktif</span>
                          @endif
                        </td>
                        <td>
                          <form method="POST" action="{{ route('admin.pages.product-detail.comments.toggle', $comment) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-{{ $comment->is_active ? 'danger' : 'success' }}">
                              {{ $comment->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
  @include('admin.pages.partials.section-editor-script')
@endsection
