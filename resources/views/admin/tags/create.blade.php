@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Tambah Tag</h4>
              <form method="POST" action="{{ route('admin.tags.store') }}">
                @csrf
                <div class="form-group">
                  <label for="name">Nama</label>
                  <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                  <label for="themes">Theme</label>
                  <select name="themes[]" id="themes" class="form-control" multiple>
                    @foreach($themes as $theme)
                      <option value="{{ $theme->id }}">{{ $theme->display_name }}</option>
                    @endforeach
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
