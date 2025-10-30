@php
    $startsAt = old('starts_at');
    $endsAt = old('ends_at');
    $startsAt = $startsAt !== null ? $startsAt : optional($promotion->starts_at)->format('Y-m-d\TH:i');
    $endsAt = $endsAt !== null ? $endsAt : optional($promotion->ends_at)->format('Y-m-d\TH:i');
    $selected = collect(old('product_ids', $selectedProducts ?? []))->map(fn ($id) => (int) $id)->all();
    $selectedAudience = old('audience_type', $promotion->audience_type ?? \App\Models\Promotion::AUDIENCE_ALL);
    $selectedUsers = collect(old('user_ids', $selectedUsers ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

@csrf
@if($promotion->exists)
    @method('PUT')
@endif
<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="promotion-name">Nama Promo</label>
      <input type="text" class="form-control" id="promotion-name" name="name" value="{{ old('name', $promotion->name) }}" required>
      @error('name')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="promotion-type">Jenis Diskon</label>
      <select class="form-control" id="promotion-type" name="discount_type" required>
        <option value="{{ \App\Models\Promotion::TYPE_PERCENTAGE }}" @selected(old('discount_type', $promotion->discount_type) === \App\Models\Promotion::TYPE_PERCENTAGE)>Persentase</option>
        <option value="{{ \App\Models\Promotion::TYPE_FIXED }}" @selected(old('discount_type', $promotion->discount_type) === \App\Models\Promotion::TYPE_FIXED)>Potongan Nominal</option>
      </select>
      @error('discount_type')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="promotion-value">Nilai Diskon</label>
      <div class="input-group">
        <input type="number" step="0.01" min="0" class="form-control" id="promotion-value" name="discount_value" value="{{ old('discount_value', $promotion->discount_value) }}" required>
        <div class="input-group-append">
          <span class="input-group-text" data-discount-label>%</span>
        </div>
      </div>
      <small class="form-text text-muted">Untuk persentase, isi 0-100. Untuk potongan nominal gunakan nilai rupiah.</small>
      @error('discount_value')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="promotion-audience">Target Pengguna</label>
      <select class="form-control" id="promotion-audience" name="audience_type" required>
        <option value="{{ \App\Models\Promotion::AUDIENCE_ALL }}" @selected($selectedAudience === \App\Models\Promotion::AUDIENCE_ALL)>Semua Pengguna</option>
        <option value="{{ \App\Models\Promotion::AUDIENCE_REGISTERED }}" @selected($selectedAudience === \App\Models\Promotion::AUDIENCE_REGISTERED)>Pengguna Terdaftar</option>
        <option value="{{ \App\Models\Promotion::AUDIENCE_SELECTED }}" @selected($selectedAudience === \App\Models\Promotion::AUDIENCE_SELECTED)>Pengguna Tertentu</option>
      </select>
      <small class="form-text text-muted">Pilih "Pengguna Terdaftar" untuk promo khusus member atau "Pengguna Tertentu" untuk memilih user tertentu.</small>
      @error('audience_type')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
  <div class="col-md-6" id="promotion-users-group" style="{{ $selectedAudience === \App\Models\Promotion::AUDIENCE_SELECTED ? '' : 'display: none;' }}">
    <div class="form-group">
      <label for="promotion-users">Pengguna yang Mendapat Promo</label>
      <select multiple size="8" class="form-control" id="promotion-users" name="user_ids[]">
        @foreach($users as $user)
          <option value="{{ $user->id }}" @selected(in_array($user->id, $selectedUsers, true))>{{ $user->name }} ({{ $user->email }})</option>
        @endforeach
      </select>
      <small class="form-text text-muted">Promo hanya berlaku untuk pengguna yang dipilih ketika opsi "Pengguna Tertentu" digunakan.</small>
      @error('user_ids')<small class="text-danger d-block">{{ $message }}</small>@enderror
      @error('user_ids.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="promotion-start">Mulai Berlaku</label>
      <input type="datetime-local" class="form-control" id="promotion-start" name="starts_at" value="{{ $startsAt }}">
      <small class="form-text text-muted">Kosongkan bila promo langsung aktif.</small>
      @error('starts_at')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="promotion-end">Berakhir</label>
      <input type="datetime-local" class="form-control" id="promotion-end" name="ends_at" value="{{ $endsAt }}">
      <small class="form-text text-muted">Kosongkan bila promo berlaku tanpa batas.</small>
      @error('ends_at')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="promotion-products">Produk yang Mengikuti Promo</label>
      <select multiple size="10" class="form-control" id="promotion-products" name="product_ids[]">
        @foreach($products as $product)
          <option value="{{ $product->id }}" @selected(in_array($product->id, $selected, true))>{{ $product->name }} — Rp {{ number_format($product->price, 0, ',', '.') }}</option>
        @endforeach
      </select>
      <small class="form-text text-muted">Secara default semua produk aktif dipilih. Gunakan Ctrl/Cmd untuk memilih lebih dari satu.</small>
      @error('product_ids')<small class="text-danger d-block">{{ $message }}</small>@enderror
      @error('product_ids.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>
</div>
<div class="row mt-4">
  <div class="col-md-12 text-right">
    <a href="{{ route('promotions.index') }}" class="btn btn-light mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
  </div>
</div>
