@extends('layout.admin')

@section('content')
@php
  $isCustomerView = $currentView === 'customers';
  $pageTitle = $isCustomerView ? 'Konsumen' : 'Tim Saya';
  $pageDescription = $isCustomerView
    ? 'Lihat daftar pelanggan dengan peran Basic.'
    : 'Kelola peran administrator, product manager, dan order manager.';
  $formAction = route('admin.users.index', array_filter(['view' => $currentView]));
@endphp

<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header d-flex align-items-center justify-content-between">
      <div>
        <h3 class="page-title mb-1">{{ $pageTitle }}</h3>
        <p class="text-muted mb-0">{{ $pageDescription }}</p>
      </div>
      @unless($isCustomerView)
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah Pengguna</a>
      @endunless
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
      <div class="card-body">
        <form method="GET" action="{{ $formAction }}" class="mb-3">
          @if($currentView)
            <input type="hidden" name="view" value="{{ $currentView }}">
          @endif
          <div class="row g-2 align-items-end">
            <div class="col-md-5">
              <label for="search" class="form-label">Pencarian</label>
              <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama atau email">
            </div>
            <div class="col-md-4">
              <label for="role" class="form-label">Peran</label>
              <select name="role" id="role" class="form-control">
                <option value="">Semua Peran</option>
                @php
                  $roleOptions = $isCustomerView
                    ? [\App\Models\User::ROLE_BASIC => 'Basic']
                    : $managedRoles;
                @endphp
                @foreach($roleOptions as $roleValue => $label)
                  <option value="{{ $roleValue }}" {{ request('role') === $roleValue ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button class="btn btn-primary flex-grow-1">Filter</button>
              <a href="{{ route('admin.users.index', array_filter(['view' => $currentView])) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Peran</th>
                <th>Dibuat</th>
                <th class="text-right" style="width: 160px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php
                $roleLabels = array_merge($managedRoles, [\App\Models\User::ROLE_BASIC => 'Basic']);
              @endphp
              @forelse($users as $user)
                <tr>
                  <td>{{ $user->name }}</td>
                  <td>{{ $user->email }}</td>
                  <td>
                    <span class="badge {{ $user->isAdministrator() ? 'badge-primary' : ($user->isProductManager() ? 'badge-info' : ($user->isOrderManager() ? 'badge-warning' : 'badge-light')) }}">
                      {{ $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                  </td>
                  <td>{{ $user->created_at?->format('d M Y') }}</td>
                  <td class="text-right">
                    @if($user->isBasic())
                      <span class="text-muted">Tidak tersedia</span>
                    @else
                      <div class="d-inline-flex gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                      </div>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Belum ada pengguna.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">
            Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
          </div>
          <div>
            {{ $users->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
