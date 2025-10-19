@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Kategori Galeri</h3>
      <a href="{{ route('admin.gallery.categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Slug</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($categories as $category)
                <tr>
                  <td>{{ $category->name }}</td>
                  <td>{{ $category->slug }}</td>
                  <td class="text-right">
                    <a href="{{ route('admin.gallery.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                    <form action="{{ route('admin.gallery.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">Belum ada kategori galeri.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
