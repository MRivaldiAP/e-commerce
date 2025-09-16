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

              <form method="GET" action="{{ route('admin.themes.index') }}" class="mb-3">
                <div class="form-group">
                  <label for="tag_filter">Filter Tag</label>
                  <select name="tag" id="tag_filter" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    @foreach($tags as $tag)
                      <option value="{{ $tag->id }}" {{ $selectedTag == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                    @endforeach
                  </select>
                </div>
              </form>

              <form method="POST" action="{{ route('admin.themes.update') }}">
                @csrf
                <div class="form-group">
                  <label for="theme">Tema</label>
                  <select name="theme" id="theme" class="form-control">
                    @foreach($themes as $theme)
                      <option value="{{ $theme->name }}" data-preview="{{ route('admin.themes.preview', $theme->name) }}" {{ $active === $theme->name ? 'selected' : '' }}>{{ $theme->display_name }}</option>
                    @endforeach
                  </select>
                </div>
                <iframe id="theme-preview" src="{{ $preview }}" class="w-100 mt-3" style="height:400px; border:1px solid #ddd;"></iframe>
                <button type="submit" class="btn btn-primary mt-3">Simpan</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script>
    document.getElementById('theme').addEventListener('change', function () {
      const preview = this.options[this.selectedIndex].dataset.preview;
      document.getElementById('theme-preview').src = preview;
    });
  </script>
@endsection

