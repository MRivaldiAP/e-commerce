@extends('layout.admin')
@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Kategori</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Katalog</a></li>
          <li class="breadcrumb-item active" aria-current="page">Kategori</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h4 class="card-title mb-0">Daftar Kategori</h4>
                <small class="text-muted">Kelola kategori produk</small>
              </div>
              <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Tambah Kategori</a>
            </div>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Produk</th>
                    <th style="width:130px">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($categories as $category)
                    <tr>
                      <td>{{ $category->name }}</td>
                      <td>{{ $category->slug }}</td>
                      <td>{{ $category->products_count }}</td>
                      <td>
                        <div class="d-flex flex-nowrap" style="gap:4px;">
                          <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>
                          <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus kategori ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Hapus"><i class="mdi mdi-delete"></i></button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center">Kategori tidak ditemukan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
