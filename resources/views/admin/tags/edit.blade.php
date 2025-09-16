@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Edit Tag</h4>
              <form method="POST" action="{{ route('admin.tags.update', $tag) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                  <label for="name">Nama</label>
                  <input type="text" name="name" id="name" class="form-control" value="{{ $tag->name }}" required>
                </div>
                <div class="form-group">
                  <label for="themes">Theme</label>
                  <select name="themes[]" id="themes" class="form-control" multiple>
                    @foreach($themes as $theme)
                      <option value="{{ $theme->id }}" {{ $tag->themes->contains($theme->id) ? 'selected' : '' }}>{{ $theme->display_name }}</option>
                    @endforeach
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
