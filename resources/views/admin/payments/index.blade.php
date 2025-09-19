@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Pengaturan Pembayaran</h4>
              <p class="card-description mb-4">Pilih gateway pembayaran yang ingin digunakan dan lengkapi kredensial yang dibutuhkan.</p>

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

              <form method="POST" action="{{ route('admin.payments.update') }}" id="payment-settings-form">
                @csrf

                <div class="form-group">
                  <label for="gateway">Gateway Aktif</label>
                  <select id="gateway" name="gateway" class="form-control">
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
                    $selectedMethods = old('gateway') === $key ? (array) old('methods', []) : ($methodSelections[$key] ?? []);
                    if(empty($selectedMethods) && !empty($methodSelections[$key] ?? [])) {
                      $selectedMethods = $methodSelections[$key];
                    }
                  @endphp
                  <div class="payment-gateway-section" data-gateway-section="{{ $key }}" style="display: none;">
                    <div class="mb-4">
                      <h5 class="font-weight-semibold mb-2">{{ $gateway->label() }}</h5>
                      <p class="text-muted">{{ $gateway->description() }}</p>
                    </div>

                    <div class="form-group">
                      <label class="d-block">Metode Pembayaran</label>
                      <small class="form-text text-muted mb-2">Pilih metode yang ingin ditampilkan ke pelanggan.</small>
                      <div class="row">
                        @foreach($gateway->availableMethods() as $methodKey => $method)
                          <div class="col-md-6">
                            <div class="form-check form-check-info">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="methods[]" value="{{ $methodKey }}" {{ in_array($methodKey, $selectedMethods, true) ? 'checked' : '' }}>
                                <span class="font-weight-medium">{{ $method['label'] }}</span>
                                <small class="d-block text-muted">{{ $method['description'] }}</small>
                              </label>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>

                    <div class="row">
                      @foreach($gateway->configFields() as $field)
                        @php
                          $fieldName = 'config[' . $field['key'] . ']';
                          $inputId = $key . '_' . $field['key'];
                          $value = old('gateway') === $key
                            ? old('config.' . $field['key'], $sectionConfig[$field['key']] ?? null)
                            : ($sectionConfig[$field['key']] ?? null);
                        @endphp
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="{{ $inputId }}">{{ $field['label'] }}</label>
                            @if(($field['type'] ?? 'text') === 'select')
                              <select name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control">
                                @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
                                  <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                @endforeach
                              </select>
                            @elseif(($field['type'] ?? 'text') === 'toggle')
                              <div class="form-check form-switch">
                                <input type="hidden" name="{{ $fieldName }}" value="0">
                                <input type="checkbox" class="form-check-input" id="{{ $inputId }}" name="{{ $fieldName }}" value="1" {{ $value ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $inputId }}">{{ $field['label'] }}</label>
                              </div>
                            @else
                              <input type="{{ $field['type'] ?? 'text' }}" class="form-control" id="{{ $inputId }}" name="{{ $fieldName }}" value="{{ $value }}" autocomplete="off">
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
      const select = document.getElementById('gateway');
      const sections = document.querySelectorAll('[data-gateway-section]');

      function toggleSections(active) {
        sections.forEach(section => {
          const isActive = section.getAttribute('data-gateway-section') === active;
          section.style.display = isActive ? 'block' : 'none';
          const inputs = section.querySelectorAll('input, select, textarea');
          inputs.forEach(input => {
            if (isActive) {
              input.removeAttribute('disabled');
            } else {
              input.setAttribute('disabled', 'disabled');
            }
          });
        });
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
