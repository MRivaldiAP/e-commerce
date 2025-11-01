@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Tambah Tenant</h4>
              <form action="{{ route('admin.tenants.store') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="tenant-id">ID Tenant</label>
                  <input type="text" id="tenant-id" name="id" value="{{ old('id') }}" class="form-control @error('id') is-invalid @enderror" placeholder="contoh: tenant-demo" required>
                  @error('id')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="tenant-name">Nama Tenant</label>
                  <input type="text" id="tenant-name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                  @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="tenant-email">Email Kontak (opsional)</label>
                  <input type="email" id="tenant-email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                  @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="tenant-domain">Domain</label>
                  <input type="text" id="tenant-domain" name="domain" value="{{ old('domain') }}" class="form-control @error('domain') is-invalid @enderror" placeholder="contoh: demo.example.com" required>
                  @error('domain')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>

                <hr>
                <h4 class="mb-3">Administrator</h4>

                <div class="form-group">
                  <label for="admin-name">Nama Administrator</label>
                  <input type="text" id="admin-name" name="admin_name" value="{{ old('admin_name') }}" class="form-control @error('admin_name') is-invalid @enderror" required>
                  @error('admin_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="admin-email">Email Administrator</label>
                  <input type="email" id="admin-email" name="admin_email" value="{{ old('admin_email') }}" class="form-control @error('admin_email') is-invalid @enderror" required>
                  @error('admin_email')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="admin-password">Kata Sandi Administrator</label>
                  <input type="password" id="admin-password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required>
                  @error('admin_password')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="admin-password-confirmation">Konfirmasi Kata Sandi Administrator</label>
                  <input type="password" id="admin-password-confirmation" name="admin_password_confirmation" class="form-control @error('admin_password_confirmation') is-invalid @enderror" required>
                  @error('admin_password_confirmation')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="d-flex justify-content-between">
                  <a href="{{ route('admin.tenants.index') }}" class="btn btn-light">Batal</a>
                  <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
