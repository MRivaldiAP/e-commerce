@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Item Galeri</h3>
      <a href="{{ route('admin.gallery.items.create') }}" class="btn btn-primary">Tambah Item</a>
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
                <th>Judul</th>
                <th>Kategori</th>
                <th>Gambar</th>
                <th>Urutan</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($items as $item)
                <tr>
                  <td>{{ $item->title }}</td>
                  <td>{{ $item->category?->name ?? 'Tanpa Kategori' }}</td>
                  <td>
                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" style="width:80px;height:60px;object-fit:cover;border-radius:4px;">
                  </td>
                  <td>{{ $item->position }}</td>
                  <td class="text-right">
                    <a href="{{ route('admin.gallery.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                    <form action="{{ route('admin.gallery.items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus item galeri ini?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">Belum ada item galeri.</td>
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
