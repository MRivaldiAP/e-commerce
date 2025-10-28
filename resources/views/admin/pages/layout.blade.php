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
                @elseif ($element['type'] === 'select')
                  <label>{{ $element['label'] }}</label>
                  @php
                    $options = $element['options'] ?? [];
                    if ($options === '@theme-variations') {
                      $options = \App\Support\ThemeVariation::options($theme);
                    }
                    $defaultValue = $element['default'] ?? null;
                    if ($defaultValue === '@theme-variation-default') {
                      $defaultValue = \App\Support\ThemeVariation::defaultKey($theme);
                    }
                    $currentValue = $settings[$element['id']] ?? $defaultValue;
                    if ($options !== [] && ! array_key_exists($currentValue, $options)) {
                      $currentValue = array_key_first($options);
                    }
                  @endphp
                  <select class="form-select" data-key="{{ $element['id'] }}">
                    @foreach ($options as $value => $label)
                      <option value="{{ $value }}" {{ (string) $currentValue === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                @elseif ($element['type'] === 'image')
                  <label>{{ $element['label'] }}</label>
                  <input type="file" class="form-control-file" data-key="{{ $element['id'] }}">
                  @if (!empty($settings[$element['id']]))
                    <img src="{{ asset('storage/' . $settings[$element['id']]) }}" alt="Preview" class="img-fluid mt-2 rounded" style="max-height:120px; object-fit:contain;">
                  @endif
                @elseif ($element['type'] === 'repeatable')
                  @php
                    $items = json_decode($settings[$element['id']] ?? '[]', true);
                    $items = is_array($items) ? $items : [];
                    $fields = $element['fields'] ?? [];
                  @endphp
                  @if (!empty($element['label']))
                    <label class="form-label">{{ $element['label'] }}</label>
                  @endif
                  <div data-repeatable="{{ $element['id'] }}" data-fields='@json($fields)'>
                    <div class="repeatable-items"></div>
                    <button type="button" class="btn btn-sm btn-secondary add-item">Tambah Tombol</button>
                    <textarea class="d-none" data-key="{{ $element['id'] }}">{{ json_encode($items) }}</textarea>
                  </div>
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
  iframe.contentWindow.location.reload();
}, 500);

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
    fetch('{{ route('admin.pages.layout.update') }}', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': csrf},
      body: formData
    }).then(() => {
      triggerPreviewReload();
    });
  });
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
      if(!field || typeof field !== 'object'){
        return;
      }

      const name = field.name || '';
      const type = field.type || 'text';
      const placeholder = field.placeholder || '';
      const value = data[name] || '';

      if(type === 'textarea'){
        html += `<textarea class="form-control mb-1" data-field="${name}" placeholder="${placeholder}">${value}</textarea>`;
        return;
      }

      if(type === 'select'){
        const options = Array.isArray(field.options) ? field.options : [];
        let optionsHtml = `<option value="">${placeholder || 'Pilih opsi'}</option>`;
        let hasSelectedValue = value === '';

        options.forEach(function(option){
          if(option === null || option === undefined){
            return;
          }

          let optionValue;
          let optionLabel;

          if(typeof option === 'object'){
            optionValue = option.value;
            optionLabel = option.label || option.value;
          }else{
            optionValue = option;
            optionLabel = option;
          }

          if(optionValue === undefined){
            return;
          }

          const selected = optionValue === value;
          if(selected){
            hasSelectedValue = true;
          }

          optionsHtml += `<option value="${optionValue}"${selected ? ' selected' : ''}>${optionLabel}</option>`;
        });

        if(value && !hasSelectedValue){
          optionsHtml += `<option value="${value}" selected>${value}</option>`;
        }

        html += `<select class="form-control mb-1" data-field="${name}">${optionsHtml}</select>`;
        return;
      }

      html += `<input type="text" class="form-control mb-1" data-field="${name}" placeholder="${placeholder}" value="${value}">`;
    });

    html += '<button type="button" class="btn btn-sm btn-outline-danger remove-item">Hapus</button>';
    div.innerHTML = html;

    return div;
  }

  function sync(){
    const data = [];
    itemsContainer.querySelectorAll('.repeatable-item').forEach(function(item){
      const row = {};
      item.querySelectorAll('[data-field]').forEach(function(input){
        row[input.getAttribute('data-field')] = input.value;
      });
      data.push(row);
    });

    hidden.value = JSON.stringify(data);
    hidden.dispatchEvent(new Event('change'));
  }

  const addButton = wrapper.querySelector('.add-item');
  if(addButton){
    addButton.addEventListener('click', function(){
      itemsContainer.appendChild(buildItem());
    });
  }

  itemsContainer.addEventListener('input', sync);
  itemsContainer.addEventListener('change', sync);
  itemsContainer.addEventListener('click', function(event){
    if(event.target.classList.contains('remove-item')){
      event.target.closest('.repeatable-item').remove();
      sync();
    }
  });

  try {
    JSON.parse(hidden.value || '[]').forEach(function(item){
      itemsContainer.appendChild(buildItem(item));
    });
  } catch (error) {}

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
