@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Pilih Tema</h4>
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              <form method="POST" action="{{ route('admin.themes.update') }}">
                @csrf
                <div class="form-group">
                  <label for="theme">Tema</label>
                  <select name="theme" id="theme" class="form-control">
                    @foreach($themes as $theme)
                      <option value="{{ $theme }}" {{ $active === $theme ? 'selected' : '' }}>{{ ucfirst(str_replace('theme-', '', $theme)) }}</option>
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

