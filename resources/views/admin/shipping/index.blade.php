@extends('layout.admin')

@section('content')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Pengaturan Pengiriman</h4>
              <p class="card-description mb-4">Aktifkan layanan pengiriman toko, pilih gateway yang tersedia, dan simpan kredensial yang dibutuhkan untuk menghitung ongkos kirim.</p>

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
                  <div class="form-check form-check-flat form-check-primary">
                    <label class="form-check-label" for="shipping-enabled">
                      <input type="checkbox" class="form-check-input" id="shipping-enabled" name="enabled" value="1" {{ old('enabled', $enabled ? 1 : 0) ? 'checked' : '' }}>
                      Aktifkan Pengiriman
                      <i class="input-helper"></i>
                    </label>
                  </div>
                  <small class="form-text text-muted">Saat dinonaktifkan, pelanggan akan langsung diarahkan ke halaman pembayaran tanpa langkah pengiriman.</small>
                </div>

                <div class="form-group">
                  <label for="shipping-provider">Penyedia Pengiriman</label>
                  <select id="shipping-provider" name="provider" class="form-control">
                    <option value="">-- Pilih Gateway --</option>
                    @foreach($gateways as $key => $gateway)
                      <option value="{{ $key }}" {{ old('provider', $activeKey) === $key ? 'selected' : '' }}>{{ $gateway->label() }}</option>
                    @endforeach
                  </select>
                  <small class="form-text text-muted">Pilih gateway yang ingin diintegrasikan. Untuk saat ini hanya RajaOngkir yang tersedia.</small>
                </div>

                @foreach($gateways as $key => $gateway)
                  @php
                    $sectionConfig = $configs[$key] ?? [];
                  @endphp
                  <div class="shipping-gateway-section" data-shipping-gateway="{{ $key }}" style="display:none;">
                    <div class="mb-4">
                      <h5 class="font-weight-semibold mb-1">{{ $gateway->label() }}</h5>
                      <p class="text-muted mb-0">{{ $gateway->description() }}</p>
                    </div>

                    <div class="row">
                      @foreach($gateway->configFields() as $field)
                        @php
                          $fieldName = 'config[' . $field['key'] . ']';
                          $inputId = $key . '_' . $field['key'];
                          $value = old('provider') === $key
                            ? old('config.' . $field['key'], $sectionConfig[$field['key']] ?? ($field['default'] ?? null))
                            : ($sectionConfig[$field['key']] ?? ($field['default'] ?? null));
                        @endphp
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="{{ $inputId }}">{{ $field['label'] }}</label>
                            @switch($field['type'] ?? 'text')
                              @case('select')
                                <select name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control">
                                  @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                  @endforeach
                                </select>
                                @break

                              @case('toggle')
                              @case('checkbox')
                              @case('boolean')
                                <div class="form-check form-switch">
                                  <input type="hidden" name="{{ $fieldName }}" value="0">
                                  <input type="checkbox" class="form-check-input" id="{{ $inputId }}" name="{{ $fieldName }}" value="1" {{ $value ? 'checked' : '' }}>
                                </div>
                                @break

                              @case('password')
                                <input type="password" class="form-control" id="{{ $inputId }}" name="{{ $fieldName }}" value="{{ $value }}" autocomplete="new-password">
                                @break

                              @default
                                <input type="{{ $field['type'] ?? 'text' }}" class="form-control" id="{{ $inputId }}" name="{{ $fieldName }}" value="{{ $value }}">
                            @endswitch
                            @if(!empty($field['help']))
                              <small class="form-text text-muted">{{ $field['help'] }}</small>
                            @endif
                            @if($field['key'] === 'origin')
                              <small class="form-text text-muted">Gunakan kode kota/kecamatan dari RajaOngkir sesuai dengan tipe origin yang dipilih.</small>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>

                    <div class="alert alert-light border mt-3" role="alert">
                      <h6 class="font-weight-semibold mb-2">Tips menentukan origin RajaOngkir</h6>
                      <p class="mb-2">Kode origin mengikuti data resmi RajaOngkir. Anda dapat menggunakan endpoint wilayah yang disediakan Laravel Nusa pada halaman pengiriman pelanggan untuk mengetahui kode kota/kecamatan terlebih dahulu.</p>
                      <small class="text-muted d-block">Contoh endpoint: <code>/nusa/provinces</code>, <code>/nusa/provinces/{kode}/regencies</code>, <code>/nusa/regencies/{kode}/districts</code>.</small>
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
      const enabledToggle = document.getElementById('shipping-enabled');
      const providerSelect = document.getElementById('shipping-provider');
      const sections = document.querySelectorAll('[data-shipping-gateway]');

      function toggleSections(providerKey, enabled) {
        sections.forEach(section => {
          const isActive = enabled && section.getAttribute('data-shipping-gateway') === providerKey;
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
        if (providerSelect) {
          providerSelect.disabled = !enabled;
        }
      }

      function refresh() {
        const enabled = enabledToggle ? enabledToggle.checked : false;
        const providerKey = providerSelect ? providerSelect.value : '';
        toggleSections(providerKey, enabled && !!providerKey);
      }

      if (enabledToggle) {
        enabledToggle.addEventListener('change', refresh);
      }
      if (providerSelect) {
        providerSelect.addEventListener('change', refresh);
      }

      refresh();
    })();
  </script>
@endsection
