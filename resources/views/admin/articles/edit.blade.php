@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Edit Artikel</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.articles.index') }}">Artikel</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $article->title }}</li>
        </ol>
      </nav>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Terjadi kesalahan!</strong> Mohon periksa kembali isian Anda.
      </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      @include('admin.articles._form')
      <div class="d-flex justify-content-between flex-wrap mt-3 gap-2">
        <div>
          <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="mdi mdi-open-in-new mr-1"></i> Lihat Halaman
          </a>
        </div>
        <div class="ml-auto">
          <a href="{{ route('admin.articles.index') }}" class="btn btn-light mr-2">Kembali</a>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </div>
    </form>
</div>
</div>
@endsection

@section('script')
  @include('admin.articles.partials.ai-generator-script')
@endsection
