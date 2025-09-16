@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Tag</h4>
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              <a href="{{ route('admin.tags.create') }}" class="btn btn-primary mb-3">Tambah Tag</a>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>Theme</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($tags as $tag)
                      <tr>
                        <td>{{ $tag->name }}</td>
                        <td>{{ $tag->themes->pluck('display_name')->join(', ') }}</td>
                        <td>
                          <a href="{{ route('admin.tags.edit', $tag) }}" class="btn btn-sm btn-warning">Edit</a>
                          <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Hapus tag?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
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
