@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Manajemen Tenant</h4>
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary mb-3">Tambah Tenant</a>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nama</th>
                      <th>Domain</th>
                      <th>Dibuat Pada</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($tenants as $tenant)
                      @php($primaryDomain = $tenant->domains->first())
                      <tr>
                        <td>{{ $tenant->id }}</td>
                        <td>{{ $tenant->name ?? '-' }}</td>
                        <td>{{ $primaryDomain?->domain ?? '-' }}</td>
                        <td>{{ $tenant->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td>
                          <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-sm btn-warning">Edit</a>
                          <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Hapus tenant? Tindakan ini juga akan menghapus database tenant.');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                          </form>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="5" class="text-center">Belum ada tenant.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="mt-3">
                {{ $tenants->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
