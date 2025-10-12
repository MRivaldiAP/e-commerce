@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Pengaturan Pengiriman</h4>
              <p class="card-description mb-4">Aktifkan layanan pengiriman dan konfigurasi gateway yang tersedia.</p>

              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif

              @if($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <form method="POST" action="{{ route('admin.shipping.update') }}" id="shipping-settings-form">
                @csrf

                <div class="form-group">
                  <div class="form-check form-switch">
                    <input type="hidden" name="shipping_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="shipping-enabled" name="shipping_enabled" value="1" {{ old('shipping_enabled', $shippingEnabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="shipping-enabled">Aktifkan Pengiriman</label>
                  </div>
                </div>

                <div id="shipping-config-wrapper" style="display: {{ old('shipping_enabled', $shippingEnabled ? '1' : '0') === '1' ? 'block' : 'none' }};">
                  <div class="form-group">
                    <label for="shipping-gateway">Gateway Pengiriman</label>
                    <select id="shipping-gateway" name="gateway" class="form-control">
                      @foreach($gateways as $key => $gateway)
                        <option value="{{ $key }}" {{ old('gateway', $activeGatewayKey ?? $defaultGatewayKey) === $key ? 'selected' : '' }}>
                          {{ $gateway->label() }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  @foreach($gateways as $key => $gateway)
                    @php
                      $sectionConfig = $configs[$key] ?? [];
                    @endphp
                    <div class="shipping-gateway-section" data-shipping-section="{{ $key }}" style="display: none;">
                      <div class="mb-4">
                        <h5 class="font-weight-semibold mb-2">{{ $gateway->label() }}</h5>
                        <p class="text-muted">{{ $gateway->description() }}</p>
                      </div>

                      <div class="row">
                        @foreach($gateway->configFields() as $field)
                          @php
                            $fieldKey = $field['key'];
                            $fieldName = 'config[' . $fieldKey . ']';
                            $inputId = $key . '_' . $fieldKey;
                            $value = old('gateway') === $key
                              ? old('config.' . $fieldKey, $sectionConfig[$fieldKey] ?? null)
                              : ($sectionConfig[$fieldKey] ?? null);
                            $type = $field['type'] ?? 'text';
                            $options = $field['options'] ?? [];
                          @endphp
                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="d-block" for="{{ $inputId }}">{{ $field['label'] }}</label>
                              @if($type === 'select')
                                @php
                                  $selectAttributes = '';
                                  if ($key === 'rajaongkir' && $fieldKey === 'origin_id') {
                                    $selectAttributes .= ' data-rajaongkir-origin-select="1"';
                                  }
                                  if ($key === 'rajaongkir' && $fieldKey === 'origin_type') {
                                    $selectAttributes .= ' data-rajaongkir-origin-type="1"';
                                  }
                                @endphp
                                <select name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control" {!! $selectAttributes !!}>
                                  @foreach($options as $option)
                                    @php
                                      $optionValue = is_array($option) ? ($option['value'] ?? $option[0] ?? '') : $option;
                                      $optionLabel = is_array($option) ? ($option['label'] ?? $optionValue) : $option;
                                      $optionDataAttributes = '';
                                      if (is_array($option) && isset($option['data']) && is_array($option['data'])) {
                                        foreach ($option['data'] as $dataKey => $dataValue) {
                                          $attrKey = 'data-' . \Illuminate\Support\Str::slug($dataKey, '-');
                                          $optionDataAttributes .= ' ' . $attrKey . '="' . e($dataValue) . '"';
                                        }
                                      }
                                    @endphp
                                    <option value="{{ $optionValue }}" {!! $optionDataAttributes !!} {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                  @endforeach
                                </select>
                              @elseif($type === 'multiselect' || ($field['multiple'] ?? false))
                                <div class="border rounded p-3">
                                  @php
                                    $selected = collect((array) $value)->map(fn($item) => (string) $item)->all();
                                  @endphp
                                  @foreach($options as $option)
                                    @php
                                      $optionValue = is_array($option) ? ($option['value'] ?? '') : $option;
                                      $optionLabel = is_array($option) ? ($option['label'] ?? $optionValue) : $option;
                                    @endphp
                                    <div class="form-check">
                                      <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="{{ $fieldName }}[]" value="{{ $optionValue }}" {{ in_array((string) $optionValue, $selected, true) ? 'checked' : '' }}>
                                        {{ $optionLabel }}
                                      </label>
                                    </div>
                                  @endforeach
                                </div>
                              @elseif($type === 'toggle' || $type === 'checkbox' || $type === 'boolean')
                                <div class="form-check form-switch">
                                  <input type="hidden" name="{{ $fieldName }}" value="0">
                                  <input type="checkbox" class="form-check-input" id="{{ $inputId }}" name="{{ $fieldName }}" value="1" {{ $value ? 'checked' : '' }}>
                                </div>
                              @else
                                <input type="{{ $type }}" class="form-control" id="{{ $inputId }}" name="{{ $fieldName }}" value="{{ $value }}" autocomplete="off">
                              @endif
                              @if(!empty($field['help']))
                                <small class="form-text text-muted">{{ $field['help'] }}</small>
                              @endif
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="mt-4">
                  <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </div>
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
    (function() {
      const toggle = document.getElementById('shipping-enabled');
      const wrapper = document.getElementById('shipping-config-wrapper');
      const select = document.getElementById('shipping-gateway');
      const sections = document.querySelectorAll('[data-shipping-section]');

      function initRajaOngkirOrigin(section) {
        if (!section) return;

        const originSelect = section.querySelector('[data-rajaongkir-origin-select]');
        if (!originSelect) return;

        const originTypeSelect = section.querySelector('[data-rajaongkir-origin-type]');

        const applyFilter = () => {
          const desiredType = originTypeSelect ? originTypeSelect.value : 'city';
          let hasSelected = false;

          Array.from(originSelect.options).forEach(option => {
            const optionType = option.getAttribute('data-origin-type') || 'city';
            const matches = optionType === desiredType;
            option.hidden = !matches;
            option.disabled = !matches;

            if (!matches && option.selected) {
              option.selected = false;
            }

            if (matches && option.selected) {
              hasSelected = true;
            }
          });

          if (!hasSelected) {
            const firstVisible = Array.from(originSelect.options).find(option => !option.hidden);
            if (firstVisible) {
              originSelect.value = firstVisible.value;
            }
          }
        };

        if (!originSelect.dataset.initialized) {
          originSelect.dataset.initialized = 'true';
          if (originTypeSelect) {
            originTypeSelect.addEventListener('change', applyFilter);
          }
        }

        applyFilter();
      }

      function toggleSections(active) {
        sections.forEach(section => {
          const isActive = section.getAttribute('data-shipping-section') === active;
          section.style.display = isActive ? 'block' : 'none';
          const inputs = section.querySelectorAll('input, select, textarea');
          inputs.forEach(input => {
            if (isActive) {
              input.removeAttribute('disabled');
            } else {
              input.setAttribute('disabled', 'disabled');
            }
          });

          if (isActive) {
            initRajaOngkirOrigin(section);
          }
        });
      }

      function toggleWrapper() {
        if (!wrapper) return;
        if (toggle && toggle.checked) {
          wrapper.style.display = 'block';
        } else {
          wrapper.style.display = 'none';
        }
      }

      if (toggle) {
        toggle.addEventListener('change', toggleWrapper);
        toggleWrapper();
      }

      if (select) {
        select.addEventListener('change', function() {
          toggleSections(this.value);
        });
        toggleSections(select.value);
      }
    })();
  </script>
@endsection
