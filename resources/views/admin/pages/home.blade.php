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
                    @include('admin.pages.partials.media-select', [
                      'element' => $element,
                      'settings' => $settings,
                      'mediaAssets' => $mediaAssets,
                    ])
                  @elseif ($element['type'] === 'repeatable')
                    @php
                      $items = json_decode($settings[$element['id']] ?? '[]', true);
                      $fields = $element['fields'] ?? [];
                    @endphp
                    <div data-repeatable="{{ $element['id'] }}" data-fields='@json($fields)'>
                      <div class="repeatable-items"></div>
                      <button type="button" class="btn btn-sm btn-secondary add-item">Add Item</button>
                      <textarea class="d-none" data-key="{{ $element['id'] }}">{{ json_encode($items) }}</textarea>
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
        <iframe id="page-preview" src="{{ url('/') }}" class="w-100 border h-100"></iframe>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
const csrf = '{{ csrf_token() }}';
const mediaOptions = @json($mediaAssets->map(function ($asset) {
  return [
    'value' => $asset->file_path,
    'label' => $asset->name,
  ];
})->values());

document.querySelectorAll('#elements [data-key]').forEach(function(input){
  input.addEventListener('change', function(){
    const key = this.getAttribute('data-key');
    const formData = new FormData();
    formData.append('key', key);
    if(this.type === 'checkbox'){
      formData.append('value', this.checked ? 1 : 0);
    }else{
      formData.append('value', this.value);
    }
    fetch('{{ route('admin.pages.home.update') }}', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': csrf},
      body: formData
    }).then(() => {
      document.getElementById('page-preview').contentWindow.location.reload();
    });
  });
});

document.querySelectorAll('[data-repeatable]').forEach(function(wrapper){
  const itemsContainer = wrapper.querySelector('.repeatable-items');
  const hidden = wrapper.querySelector('[data-key]');
  const fields = JSON.parse(wrapper.getAttribute('data-fields') || '[]');

  function buildItem(data = {}){
    const div = document.createElement('div');
    div.className = 'repeatable-item mb-2';
    let html = '';
    fields.forEach(function(field){
      const fieldType = field.type || 'text';
      if(fieldType === 'textarea'){
        html += `<textarea class="form-control mb-1" data-field="${field.name}" placeholder="${field.placeholder}">${data[field.name] || ''}</textarea>`;
      }else if(fieldType === 'image'){
        const value = data[field.name] || '';
        let optionsHtml = `<option value="">${field.placeholder || 'Pilih media'}</option>`;
        let hasSelected = value === '';
        mediaOptions.forEach(function(option){
          const selected = option.value === value;
          if(selected){ hasSelected = true; }
          optionsHtml += `<option value="${option.value}"${selected ? ' selected' : ''}>${option.label}</option>`;
        });
        if(value && !hasSelected){
          optionsHtml += `<option value="${value}" selected>${value}</option>`;
        }
        html += `<select class="form-control mb-1" data-field="${field.name}">${optionsHtml}</select>`;
      }else{
        html += `<input type="text" class="form-control mb-1" data-field="${field.name}" placeholder="${field.placeholder}" value="${data[field.name] || ''}">`;
      }
    });
    html += '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>';
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

  // initialize existing
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
