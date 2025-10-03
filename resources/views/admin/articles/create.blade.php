@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Tulis Artikel Baru</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.articles.index') }}">Artikel</a></li>
          <li class="breadcrumb-item active" aria-current="page">Buat</li>
        </ol>
      </nav>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Terjadi kesalahan!</strong> Mohon periksa kembali isian Anda.
      </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
      @csrf
      @include('admin.articles._form')
      <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('admin.articles.index') }}" class="btn btn-light mr-2">Batal</a>
        <button type="submit" class="btn btn-primary">Publikasikan Artikel</button>
      </div>
    </form>
</div>
</div>
@endsection

@section('script')
  @include('admin.articles.partials.ai-generator-script')
@endsection
