@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Edit Tenant</h4>
              <form action="{{ route('admin.tenants.update', $tenant) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                  <label for="tenant-id">ID Tenant</label>
                  <input type="text" id="tenant-id" value="{{ $tenant->id }}" class="form-control" disabled>
                </div>
                <div class="form-group">
                  <label for="tenant-name">Nama Tenant</label>
                  <input type="text" id="tenant-name" name="name" value="{{ old('name', $tenant->name ?? $tenant->id) }}" class="form-control @error('name') is-invalid @enderror" required>
                  @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="tenant-email">Email Kontak (opsional)</label>
                  <input type="email" id="tenant-email" name="email" value="{{ old('email', $tenant->data['email'] ?? '') }}" class="form-control @error('email') is-invalid @enderror">
                  @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="tenant-domain">Domain</label>
                  <input type="text" id="tenant-domain" name="domain" value="{{ old('domain', $primaryDomain?->domain) }}" class="form-control @error('domain') is-invalid @enderror" required>
                  @error('domain')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="d-flex justify-content-between">
                  <a href="{{ route('admin.tenants.index') }}" class="btn btn-light">Batal</a>
                  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
