@php
    $settingsArray = $settings instanceof \Illuminate\Support\Collection ? $settings->toArray() : (array) $settings;
    $activeComposition = array_values($composition ?? []);
    $availableList = $availableSections ?? [];
    $defaultOrder = $defaultComposition ?? [];
    $labelMap = $sectionLabels ?? [];
    $themeKey = $theme ?? null;
@endphp

<div
  class="col-md-4"
  id="elements"
  data-section-editor="true"
  data-update="{{ $updateRoute }}"
  data-csrf="{{ csrf_token() }}"
  data-composition='@json($activeComposition)'
  data-default-composition='@json($defaultOrder)'
  data-labels='@json($labelMap)'
  data-theme="{{ $themeKey ?? '' }}"
  style="max-height:100vh; overflow-y:auto;"
>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Susunan Seksi</span>
      <button type="button" class="btn btn-sm btn-outline-secondary" data-reset-sections {{ empty($defaultOrder) ? 'disabled' : '' }}>Reset Default</button>
    </div>
    <div class="card-body">
      @if (!empty($activeComposition))
        <ol class="list-group list-group-numbered mb-3">
          @foreach ($activeComposition as $sectionKey)
            @php($label = $labelMap[$sectionKey] ?? ucfirst($sectionKey))
            <li class="list-group-item py-1">{{ $label }}</li>
          @endforeach
        </ol>
      @else
        <p class="text-muted mb-3">Belum ada seksi yang aktif.</p>
      @endif

      <label class="form-label">Tambah Seksi</label>
      <div class="input-group">
        <select class="form-control" data-section-picker>
          <option value="">Pilih seksi...</option>
          @foreach ($availableList as $available)
            <option value="{{ $available['key'] }}">{{ $available['label'] }}</option>
          @endforeach
        </select>
        <button type="button" class="btn btn-primary" data-add-section {{ empty($availableList) ? 'disabled' : '' }}>Tambah</button>
      </div>
    </div>
  </div>

  @foreach ($sections as $key => $section)
    <div class="card mb-3" data-section="{{ $key }}">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $section['label'] }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-section="{{ $key }}">Hapus</button>
      </div>
      <div class="card-body">
        @foreach ($section['elements'] as $element)
          @php
            $type = $element['type'] ?? 'text';
            $elementId = $element['id'];
            $currentValue = $settingsArray[$elementId] ?? '';
          @endphp

          <div class="form-group">
            @if ($type === 'checkbox')
              <div class="form-check">
                <input class="form-check-input" type="checkbox" data-key="{{ $elementId }}" {{ $currentValue == '1' ? 'checked' : '' }}>
                <label class="form-check-label">{{ $element['label'] }}</label>
              </div>
            @elseif ($type === 'text')
              <label>{{ $element['label'] }}</label>
              <input type="text" class="form-control" data-key="{{ $elementId }}" value="{{ $currentValue }}">
            @elseif ($type === 'textarea')
              <label>{{ $element['label'] }}</label>
              <textarea class="form-control" data-key="{{ $elementId }}">{{ $currentValue }}</textarea>
            @elseif ($type === 'image')
              <label>{{ $element['label'] }}</label>
              <input type="file" class="form-control-file" data-key="{{ $elementId }}">
              @if (!empty($currentValue))
                <img src="{{ \Illuminate\Support\Str::startsWith($currentValue, ['http://', 'https://']) ? $currentValue : asset('storage/' . $currentValue) }}" alt="Preview" class="img-fluid mt-2 rounded" style="max-height:120px; object-fit:contain;">
              @endif
            @elseif ($type === 'select')
              <label>{{ $element['label'] }}</label>
              @php
                $options = $element['options'] ?? [];
                if ($options === '@theme-variations' && $themeKey) {
                  $options = \App\Support\ThemeVariation::options($themeKey);
                }
                $defaultValue = $element['default'] ?? null;
                if ($defaultValue === '@theme-variation-default' && $themeKey) {
                  $defaultValue = \App\Support\ThemeVariation::defaultKey($themeKey);
                }
                $selected = $currentValue !== '' ? $currentValue : $defaultValue;
              @endphp
              <select class="form-select" data-key="{{ $elementId }}">
                @foreach ($options as $value => $label)
                  @php($optionValue = is_int($value) ? $label : $value)
                  <option value="{{ $optionValue }}" {{ (string) $selected === (string) $optionValue ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            @elseif ($type === 'repeatable')
              @php
                $items = json_decode($currentValue ?: '[]', true);
                $items = is_array($items) ? $items : [];
                $fields = $element['fields'] ?? [];
              @endphp
              @if (!empty($element['label']))
                <label class="form-label">{{ $element['label'] }}</label>
              @endif
              <div data-repeatable="{{ $elementId }}" data-fields='@json($fields)'>
                <div class="repeatable-items"></div>
                <button type="button" class="btn btn-sm btn-secondary add-item">Tambah Item</button>
                <textarea class="d-none" data-key="{{ $elementId }}">{{ json_encode($items) }}</textarea>
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
