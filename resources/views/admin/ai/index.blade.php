@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Pengaturan AI</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasbor</a></li>
          <li class="breadcrumb-item active" aria-current="page">AI</li>
        </ol>
      </nav>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Terjadi kesalahan.</strong> Mohon periksa kembali isian Anda.
      </div>
    @endif

    <form method="POST" action="{{ route('admin.ai.update') }}">
      @csrf
      <div class="row">
        <div class="col-lg-6">
          <div class="card mb-4">
            <div class="card-body">
              <h4 class="card-title">Kredensial OpenAI</h4>
              <p class="card-description">Simpan API key resmi dari akun OpenAI Anda. Kami menyimpannya secara terenkripsi di basis data.</p>
              <div class="form-group">
                <label for="openai_api_key">API Key</label>
                <input type="text" id="openai_api_key" name="openai_api_key" class="form-control @error('openai_api_key') is-invalid @enderror" value="{{ old('openai_api_key', $settings['openai_api_key']) }}" placeholder="sk-...">
                @error('openai_api_key')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <small class="text-muted">Gunakan <code>sk-</code> key dari OpenAI atau proxy kompatibel.</small>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card mb-4">
            <div class="card-body">
              <h4 class="card-title">Catatan Keamanan</h4>
              <ul class="mb-0 pl-3">
                <li>Batasi akses ke menu ini hanya untuk administrator.</li>
                <li>Pertimbangkan penggunaan <em>usage limits</em> di akun OpenAI Anda.</li>
                <li>Kunci akan digunakan untuk seluruh fitur AI di platform ini.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6">
          <div class="card mb-4">
            <div class="card-body">
              <h4 class="card-title">Generator Artikel SEO</h4>
              <p class="card-description">Atur parameter model khusus untuk pembuatan artikel otomatis di menu SEO &gt; Manajemen Artikel.</p>
              <div class="form-group">
                <label for="article_model">Model</label>
                <input type="text" id="article_model" name="article_model" class="form-control @error('article_model') is-invalid @enderror" value="{{ old('article_model', $settings['article']['model']) }}" placeholder="misal: gpt-4o-mini">
                @error('article_model')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="article_temperature">Temperature</label>
                  <input type="number" step="0.01" min="0" max="2" id="article_temperature" name="article_temperature" class="form-control @error('article_temperature') is-invalid @enderror" value="{{ old('article_temperature', $settings['article']['temperature']) }}">
                  @error('article_temperature')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group col-md-4">
                  <label for="article_top_p">Top P</label>
                  <input type="number" step="0.01" min="0" max="1" id="article_top_p" name="article_top_p" class="form-control @error('article_top_p') is-invalid @enderror" value="{{ old('article_top_p', $settings['article']['top_p']) }}">
                  @error('article_top_p')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group col-md-4">
                  <label for="article_max_tokens">Max Tokens</label>
                  <input type="number" min="1" id="article_max_tokens" name="article_max_tokens" class="form-control @error('article_max_tokens') is-invalid @enderror" value="{{ old('article_max_tokens', $settings['article']['max_tokens']) }}" placeholder="Kosongkan untuk default model">
                  @error('article_max_tokens')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="article_presence_penalty">Presence Penalty</label>
                  <input type="number" step="0.1" min="-2" max="2" id="article_presence_penalty" name="article_presence_penalty" class="form-control @error('article_presence_penalty') is-invalid @enderror" value="{{ old('article_presence_penalty', $settings['article']['presence_penalty']) }}">
                  @error('article_presence_penalty')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group col-md-6">
                  <label for="article_frequency_penalty">Frequency Penalty</label>
                  <input type="number" step="0.1" min="-2" max="2" id="article_frequency_penalty" name="article_frequency_penalty" class="form-control @error('article_frequency_penalty') is-invalid @enderror" value="{{ old('article_frequency_penalty', $settings['article']['frequency_penalty']) }}">
                  @error('article_frequency_penalty')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card mb-4">
            <div class="card-body">
              <h4 class="card-title">Report Reader (Segera Hadir)</h4>
              <p class="card-description">Siapkan parameter untuk modul pembaca laporan ketika fitur ini diluncurkan.</p>
              <div class="form-group">
                <label for="report_model">Model</label>
                <input type="text" id="report_model" name="report_model" class="form-control @error('report_model') is-invalid @enderror" value="{{ old('report_model', $settings['report']['model']) }}" placeholder="misal: gpt-4o-mini">
                @error('report_model')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="report_temperature">Temperature</label>
                  <input type="number" step="0.01" min="0" max="2" id="report_temperature" name="report_temperature" class="form-control @error('report_temperature') is-invalid @enderror" value="{{ old('report_temperature', $settings['report']['temperature']) }}">
                  @error('report_temperature')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group col-md-4">
                  <label for="report_top_p">Top P</label>
                  <input type="number" step="0.01" min="0" max="1" id="report_top_p" name="report_top_p" class="form-control @error('report_top_p') is-invalid @enderror" value="{{ old('report_top_p', $settings['report']['top_p']) }}">
                  @error('report_top_p')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group col-md-4">
                  <label for="report_max_tokens">Max Tokens</label>
                  <input type="number" min="1" id="report_max_tokens" name="report_max_tokens" class="form-control @error('report_max_tokens') is-invalid @enderror" value="{{ old('report_max_tokens', $settings['report']['max_tokens']) }}" placeholder="Kosongkan untuk default model">
                  @error('report_max_tokens')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="report_presence_penalty">Presence Penalty</label>
                  <input type="number" step="0.1" min="-2" max="2" id="report_presence_penalty" name="report_presence_penalty" class="form-control @error('report_presence_penalty') is-invalid @enderror" value="{{ old('report_presence_penalty', $settings['report']['presence_penalty']) }}">
                  @error('report_presence_penalty')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group col-md-6">
                  <label for="report_frequency_penalty">Frequency Penalty</label>
                  <input type="number" step="0.1" min="-2" max="2" id="report_frequency_penalty" name="report_frequency_penalty" class="form-control @error('report_frequency_penalty') is-invalid @enderror" value="{{ old('report_frequency_penalty', $settings['report']['frequency_penalty']) }}">
                  @error('report_frequency_penalty')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
      </div>
    </form>
  </div>
</div>
@endsection
