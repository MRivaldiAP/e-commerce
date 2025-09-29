@extends('layout.admin')

@php
    $articles = collect($articles ?? []);
@endphp

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-4" id="elements" style="max-height:100vh; overflow-y:auto;">
        @foreach ($sections as $key => $section)
        <div class="card mb-3" data-section="{{ $key }}">
          <div class="card-header">{{ $section['label'] }}</div>
          <div class="card-body">
            @foreach ($section['elements'] as $element)
              <div class="form-group">
                @if ($element['type'] === 'checkbox')
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" data-key="{{ $element['id'] }}" {{ ($settings[$element['id']] ?? '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $element['label'] }}</label>
                  </div>
                @elseif ($element['type'] === 'text')
                  <label>{{ $element['label'] }}</label>
                  <input type="text" class="form-control" data-key="{{ $element['id'] }}" value="{{ $settings[$element['id']] ?? '' }}">
                @elseif ($element['type'] === 'textarea')
                  <label>{{ $element['label'] }}</label>
                  <textarea class="form-control" data-key="{{ $element['id'] }}">{{ $settings[$element['id']] ?? '' }}</textarea>
                @elseif ($element['type'] === 'image')
                  <label>{{ $element['label'] }}</label>
                  <input type="file" class="form-control-file" data-key="{{ $element['id'] }}">
                @else
                  <p class="text-muted mb-0">{{ $element['label'] }}</p>
                @endif
              </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
      <div class="col-md-8 position-sticky" style="top:0;height:100vh">
        @if($previewUrl)
          <iframe id="page-preview" src="{{ $previewUrl }}" class="w-100 border h-100"></iframe>
        @else
          <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="alert alert-info mb-0">Tambahkan artikel pada halaman artikel untuk melihat pratinjau detail.</div>
          </div>
        @endif
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">Daftar Artikel Tersedia</div>
          <div class="card-body p-0">
            @if($articles->isEmpty())
              <p class="text-muted p-3 mb-0">Belum ada artikel yang dapat dipratinjau.</p>
            @else
              <div class="table-responsive">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Judul</th>
                      <th>Slug</th>
                      <th>Pratinjau</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($articles as $item)
                      <tr>
                        <td>{{ $item['title'] ?? '-' }}</td>
                        <td>{{ $item['slug'] ?? '-' }}</td>
                        <td>
                          @if(!empty($item['slug']))
                            <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" target="_blank" rel="noopener">Lihat</a>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
const csrf = '{{ csrf_token() }}';

document.querySelectorAll('#elements [data-key]').forEach(function(input){
  input.addEventListener('change', function(){
    const key = this.getAttribute('data-key');
    const formData = new FormData();
    formData.append('key', key);
    if(this.type === 'checkbox'){
      formData.append('value', this.checked ? 1 : 0);
    }else if(this.type === 'file'){
      if(this.files[0]){ formData.append('value', this.files[0]); }
    }else{
      formData.append('value', this.value);
    }

    fetch('{{ route('admin.pages.article-detail.update') }}', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': csrf},
      body: formData
    }).then(() => {
      const frame = document.getElementById('page-preview');
      if(frame){
        frame.contentWindow.location.reload();
      }
    });
  });

  if(input.type === 'file'){
    input.addEventListener('input', function(){
      const event = new Event('change');
      this.dispatchEvent(event);
    });
  }
});

document.querySelectorAll('#elements .card').forEach(function(card){
  card.addEventListener('mouseenter', function(){
    const target = card.getAttribute('data-section');
    const iframe = document.getElementById('page-preview');
    if(iframe && iframe.contentWindow){
      const section = iframe.contentWindow.document.getElementById(target);
      if(section){
        section.style.outline = '2px dashed #ff9800';
        section.scrollIntoView({behavior:'smooth'});
      }
    }
  });
  card.addEventListener('mouseleave', function(){
    const target = card.getAttribute('data-section');
    const iframe = document.getElementById('page-preview');
    if(iframe && iframe.contentWindow){
      const section = iframe.contentWindow.document.getElementById(target);
      if(section){
        section.style.outline = '';
      }
    }
  });
});
</script>
@endsection
