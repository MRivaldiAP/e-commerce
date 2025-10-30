@extends('central.layout')

@section('content')
  <div class="page-header d-flex align-items-center justify-content-between flex-wrap">
    <div>
      <h3 class="page-title mb-2">Tambah Tenant</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('central.admin.dashboard') }}">Dasbor</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.admin.tenants.index') }}">Tenant</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tambah</li>
        </ol>
      </nav>
    </div>
    <a href="{{ route('central.admin.tenants.index') }}" class="btn btn-light">Kembali</a>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Form Tenant Baru</h4>
          <form method="POST" action="{{ route('central.admin.tenants.store') }}">
            @csrf
            <div class="form-group">
              <label for="tenant-id">ID Tenant</label>
              <input type="text" name="id" id="tenant-id" class="form-control @error('id') is-invalid @enderror" value="{{ old('id') }}" placeholder="contoh: tenant-123" required>
              @error('id')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
              @enderror
              <small class="form-text text-muted">Gunakan karakter huruf, angka, dash, atau underscore.</small>
            </div>

            <div class="form-group">
              <label for="tenant-name">Nama Tenant</label>
              <input type="text" name="name" id="tenant-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
              @error('name')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group">
              <label for="tenant-domain">Domain</label>
              <input type="text" name="domain" id="tenant-domain" class="form-control @error('domain') is-invalid @enderror" value="{{ old('domain') }}" placeholder="contoh: toko-saya.localhost" required>
              @error('domain')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
              @enderror
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Simpan Tenant</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
