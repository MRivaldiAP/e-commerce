@extends('layout.admin')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Promo Produk</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">Promo</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Promo</li>
        </ol>
      </nav>
    </div>

    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Perbarui Promo</h4>
            <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST">
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
