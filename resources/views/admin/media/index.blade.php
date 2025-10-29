@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Media</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
          <li class="breadcrumb-item active" aria-current="page">Media</li>
        </ol>
      </nav>
    </div>

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row">
      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Unggah Media Baru</h4>
            <p class="card-description">Kelola aset visual website di satu tempat. Gunakan path media pada halaman Kelola Halaman atau Brand.</p>
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-5">
                  <label for="name">Nama Media</label>
                  <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Contoh: Hero Banner" required>
                </div>
                <div class="form-group col-md-5">
                  <label for="file">File</label>
                  <input type="file" id="file" name="file" class="form-control" required>
                  <small class="form-text text-muted">Format yang didukung: JPG, PNG, GIF, WEBP, SVG. Maks 5MB.</small>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Unggah</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Daftar Media</h4>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Preview</th>
                    <th>Nama</th>
                    <th>Ukuran</th>
                    <th>Dibuat</th>
                    <th>Salin</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($assets as $asset)
                    <tr>
                      <td style="width: 120px;">
                        <img src="{{ $asset->public_url }}" alt="{{ $asset->name }}" class="img-fluid rounded">
                      </td>
                      <td>{{ $asset->name }}</td>
                      <td>{{ $asset->file_size ? number_format($asset->file_size / 1024, 2) . ' KB' : '-' }}</td>
                      <td>{{ $asset->created_at->format('d M Y H:i') }}</td>
                      <td>
                        <div class="btn-group" role="group" aria-label="Salin media">
                          <button class="btn btn-sm btn-outline-secondary copy-path" data-path="{{ $asset->public_url }}">Salin URL</button>
                          <button class="btn btn-sm btn-outline-info copy-path" data-path="storage/{{ $asset->file_path }}">Salin Path</button>
                        </div>
                      </td>
                      <td>
                        <form action="{{ route('admin.media.destroy', $asset) }}" method="POST" onsubmit="return confirm('Hapus media ini?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center">Belum ada media yang diunggah.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
              {{ $assets->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.copy-path').forEach(function (button) {
      button.addEventListener('click', function () {
        const path = this.dataset.path;
        navigator.clipboard.writeText(path).then(() => {
          const originalText = this.textContent;
          this.textContent = 'Disalin!';
          this.classList.remove('btn-outline-secondary', 'btn-outline-info');
          this.classList.add('btn-success');

          setTimeout(() => {
            this.textContent = originalText;
            this.classList.remove('btn-success');
            if (originalText === 'Salin URL') {
              this.classList.add('btn-outline-secondary');
            } else {
              this.classList.add('btn-outline-info');
            }
          }, 1500);
        });
      });
    });
  });
</script>
@endsection
