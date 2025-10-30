@extends('central.layout')

@section('content')
  <div class="page-header d-flex align-items-center justify-content-between flex-wrap">
    <div>
      <h3 class="page-title mb-2">Kelola Tenant</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('central.admin.dashboard') }}">Dasbor</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tenant</li>
        </ol>
      </nav>
    </div>
    <a href="{{ route('central.admin.tenants.create') }}" class="btn btn-primary">Tambah Tenant</a>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Daftar Tenant</h4>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nama</th>
                  <th>Domain</th>
                  <th>Dibuat Pada</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tenants as $tenant)
                  <tr>
                    <td class="align-middle">{{ $tenant->id }}</td>
                    <td class="align-middle">{{ $tenant->name ?? '-' }}</td>
                    <td class="align-middle">
                      @if($tenant->domains->isNotEmpty())
                        <ul class="list-unstyled mb-0">
                          @foreach($tenant->domains as $domain)
                            <li>{{ $domain->domain }}</li>
                          @endforeach
                        </ul>
                      @else
                        <span class="text-muted">Tidak ada domain</span>
                      @endif
                    </td>
                    <td class="align-middle">{{ optional($tenant->created_at)->format('d M Y H:i') }}</td>
                    <td class="align-middle text-right">
                      <a href="{{ route('central.admin.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary mr-2">Edit</a>
                      <form action="{{ route('central.admin.tenants.destroy', $tenant) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus tenant ini? Semua data tenant akan ikut terhapus.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted">Belum ada tenant terdaftar.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($tenants->hasPages())
          <div class="card-footer">
            {{ $tenants->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
