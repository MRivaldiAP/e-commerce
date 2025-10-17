@extends('layout.admin')

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
                  @if (!empty($settings[$element['id']]))
                    <img src="{{ asset('storage/' . $settings[$element['id']]) }}" alt="Preview" class="img-fluid mt-2 rounded" style="max-height:120px; object-fit:cover;">
                  @endif
                @elseif ($element['type'] === 'repeatable')
                  @php
                    $itemsValue = json_decode($settings[$element['id']] ?? '[]', true);
                    $fields = $element['fields'] ?? [];
                  @endphp
                  <div data-repeatable="{{ $element['id'] }}" data-fields='@json($fields)'>
                    <div class="repeatable-items"></div>
                    <button type="button" class="btn btn-sm btn-secondary add-item">Tambah Item</button>
                    <textarea class="d-none" data-key="{{ $element['id'] }}">{{ json_encode($itemsValue) }}</textarea>
                  </div>
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
        <iframe id="page-preview" src="{{ $previewUrl }}" class="w-100 border h-100"></iframe>
      </div>
    </div>

  </div>
</div>
@endsection

@section('script')
<script>
const csrf = '{{ csrf_token() }}';

function debounce(fn, delay) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  }
}

const triggerPreviewReload = debounce(function(){
  const iframe = document.getElementById('page-preview');
  if (iframe && iframe.contentWindow) {
    iframe.contentWindow.location.reload();
  }
}, 500);

function updateSetting(input){
  const key = input.getAttribute('data-key');
  const formData = new FormData();
  formData.append('key', key);
  if(input.type === 'checkbox'){
    formData.append('value', input.checked ? 1 : 0);
  }else if(input.type === 'file'){
    if(input.files[0]){ formData.append('value', input.files[0]); }
  }else{
    formData.append('value', input.value);
  }

  fetch('{{ route('admin.pages.gallery.update') }}', {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': csrf},
    body: formData
  }).then(() => {
    triggerPreviewReload();
  });
}

document.querySelectorAll('#elements [data-key]').forEach(function(input){
  input.addEventListener('change', function(){
    updateSetting(this);
  });
  if(input.type === 'file'){
    input.addEventListener('input', function(){
      updateSetting(this);
    });
  }
});

document.querySelectorAll('[data-repeatable]').forEach(function(wrapper){
  const itemsContainer = wrapper.querySelector('.repeatable-items');
  const hidden = wrapper.querySelector('[data-key]');
  const fields = JSON.parse(wrapper.getAttribute('data-fields') || '[]');

  function buildItem(data = {}){
    const div = document.createElement('div');
    div.className = 'repeatable-item mb-2 border rounded p-2';
    let html = '';
    fields.forEach(function(field){
      const type = field.type || 'text';
      const name = field.name;
      const placeholder = field.placeholder || '';
      const value = data[name] || '';
      if(type === 'textarea'){
        html += `<textarea class="form-control mb-2" data-field="${name}" placeholder="${placeholder}">${value}</textarea>`;
      }else{
        html += `<input type="text" class="form-control mb-2" data-field="${name}" placeholder="${placeholder}" value="${value}">`;
      }
    });
    html += '<button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button>';
    div.innerHTML = html;
    return div;
  }

  function sync(){
    const data = [];
    itemsContainer.querySelectorAll('.repeatable-item').forEach(function(item){
      const obj = {};
      item.querySelectorAll('[data-field]').forEach(function(input){
        obj[input.getAttribute('data-field')] = input.value;
      });
      data.push(obj);
    });
    hidden.value = JSON.stringify(data);
    hidden.dispatchEvent(new Event('change'));
  }

  wrapper.querySelector('.add-item').addEventListener('click', function(){
    itemsContainer.appendChild(buildItem());
  });

  itemsContainer.addEventListener('input', sync);
  itemsContainer.addEventListener('change', sync);
  itemsContainer.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-item')){
      e.target.closest('.repeatable-item').remove();
      sync();
    }
  });

  try {
    JSON.parse(hidden.value || '[]').forEach(function(item){
      itemsContainer.appendChild(buildItem(item));
    });
  } catch(e) {}
  sync();
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
