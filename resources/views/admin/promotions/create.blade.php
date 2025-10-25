@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Promo Produk</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('promotions.index') }}">Promo</a></li>
          <li class="breadcrumb-item active" aria-current="page">Buat Promo</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Buat Promo Baru</h4>
            <p class="text-muted">Promo otomatis mencakup seluruh produk aktif, Anda dapat menyesuaikan pilihan di bawah.</p>
            <form action="{{ route('promotions.store') }}" method="POST">
              @include('admin.promotions._form')
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
  @include('admin.promotions.partials.script')
@endsection
