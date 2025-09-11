@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-4" id="elements">
        @foreach ($sections as $key => $section)
        <div class="card mb-3" data-section="{{ $key }}">
          <div class="card-header">{{ $section['label'] }}</div>
          <div class="card-body">
            @foreach ($section['elements'] as $element)
              <div class="form-group">
                @if ($element['type'] === 'checkbox')
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" checked>
                    <label class="form-check-label">{{ $element['label'] }}</label>
                  </div>
                @elseif ($element['type'] === 'text')
                  <label>{{ $element['label'] }}</label>
                  <input type="text" class="form-control">
                @elseif ($element['type'] === 'textarea')
                  <label>{{ $element['label'] }}</label>
                  <textarea class="form-control"></textarea>
                @elseif ($element['type'] === 'image')
                  <label>{{ $element['label'] }}</label>
                  <input type="file" class="form-control-file">
                @else
                  <p class="text-muted mb-0">{{ $element['label'] }}</p>
                @endif
              </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
      <div class="col-md-8">
        <iframe id="page-preview" src="{{ url('/') }}" class="w-100 border" style="height:80vh"></iframe>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
document.querySelectorAll('#elements .card').forEach(function(card){
  card.addEventListener('mouseenter', function(){
    const target = card.getAttribute('data-section');
    const iframe = document.getElementById('page-preview');
    const section = iframe.contentWindow.document.getElementById(target);
    if(section){
      section.style.outline = '2px dashed #ff9800';
      section.scrollIntoView({behavior:'smooth'});
    }
  });
  card.addEventListener('mouseleave', function(){
    const target = card.getAttribute('data-section');
    const iframe = document.getElementById('page-preview');
    const section = iframe.contentWindow.document.getElementById(target);
    if(section){
      section.style.outline = '';
    }
  });
});
</script>
@endsection
