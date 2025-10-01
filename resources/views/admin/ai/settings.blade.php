@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Pengaturan AI</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasbor</a></li>
          <li class="breadcrumb-item active" aria-current="page">Pengaturan AI</li>
        </ol>
      </nav>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Terjadi kesalahan!</strong> Mohon periksa kembali input Anda.
      </div>
    @endif

    @if (! config('services.openai.api_key'))
      <div class="alert alert-warning">
        <strong>Kunci API OpenAI belum terpasang.</strong> Tambahkan nilai <code>OPENAI_API_KEY</code> (dan bila perlu <code>OPENAI_ORGANIZATION</code>) pada file <code>.env</code> agar integrasi AI dapat digunakan.
      </div>
    @endif

    <div class="row">
      @foreach ($sections as $section => $meta)
        @php
          /** @var \App\Models\AiSetting $setting */
          $setting = $settings[$section];
        @endphp
        <div class="col-lg-6 d-flex align-items-stretch">
          <div class="card w-100 mb-4">
            <div class="card-body d-flex flex-column">
              <div class="mb-4">
                <h4 class="card-title mb-1">{{ $meta['label'] }}</h4>
                <p class="text-muted mb-0">{{ $meta['description'] }}</p>
              </div>
              <form method="POST" action="{{ route('admin.ai-settings.update', $section) }}" class="flex-grow-1 d-flex flex-column">
                @csrf
                @method('PUT')
                <input type="hidden" name="section_label" value="{{ $meta['label'] }}">
                <div class="form-group">
                  <label for="model-{{ $section }}">Model</label>
                  <input type="text" class="form-control @error('model') is-invalid @enderror" id="model-{{ $section }}" name="model" value="{{ old('model', $setting->model) }}" placeholder="mis. gpt-4o-mini">
                  <small class="form-text text-muted">Tentukan model OpenAI yang ingin digunakan untuk proses ini.</small>
                  @error('model')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="temperature-{{ $section }}">Temperature</label>
                    <input type="number" step="0.01" min="0" max="2" class="form-control @error('temperature') is-invalid @enderror" id="temperature-{{ $section }}" name="temperature" value="{{ old('temperature', $setting->temperature) }}" placeholder="0.7">
                    <small class="form-text text-muted">Semakin tinggi nilainya semakin kreatif hasilnya.</small>
                    @error('temperature')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-group col-md-6">
                    <label for="max_tokens-{{ $section }}">Max Tokens</label>
                    <input type="number" min="1" max="128000" class="form-control @error('max_tokens') is-invalid @enderror" id="max_tokens-{{ $section }}" name="max_tokens" value="{{ old('max_tokens', $setting->max_tokens) }}" placeholder="2048">
                    <small class="form-text text-muted">Batasi panjang jawaban yang diizinkan model.</small>
                    @error('max_tokens')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="top_p-{{ $section }}">Top P</label>
                    <input type="number" step="0.01" min="0" max="1" class="form-control @error('top_p') is-invalid @enderror" id="top_p-{{ $section }}" name="top_p" value="{{ old('top_p', $setting->top_p) }}" placeholder="1">
                    @error('top_p')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-group col-md-4">
                    <label for="frequency_penalty-{{ $section }}">Frequency Penalty</label>
                    <input type="number" step="0.01" min="-2" max="2" class="form-control @error('frequency_penalty') is-invalid @enderror" id="frequency_penalty-{{ $section }}" name="frequency_penalty" value="{{ old('frequency_penalty', $setting->frequency_penalty) }}" placeholder="0">
                    @error('frequency_penalty')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-group col-md-4">
                    <label for="presence_penalty-{{ $section }}">Presence Penalty</label>
                    <input type="number" step="0.01" min="-2" max="2" class="form-control @error('presence_penalty') is-invalid @enderror" id="presence_penalty-{{ $section }}" name="presence_penalty" value="{{ old('presence_penalty', $setting->presence_penalty) }}" placeholder="0">
                    @error('presence_penalty')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="mt-auto d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
