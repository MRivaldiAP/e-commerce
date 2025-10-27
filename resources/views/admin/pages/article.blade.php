@extends('layout.admin')

@php
    $articles = collect($articles ?? []);
@endphp

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      @include('admin.pages.partials.section-editor', [
        'sections' => $sections,
        'settings' => $settings,
        'availableSections' => $availableSections,
        'composition' => $composition,
        'defaultComposition' => $defaultComposition,
        'sectionLabels' => $sectionLabels,
        'updateRoute' => route('admin.pages.article.update'),
      ])
      <div class="col-md-8 position-sticky" style="top:0;height:100vh">
        @if($previewUrl)
          <iframe id="page-preview" src="{{ $previewUrl }}" class="w-100 border h-100"></iframe>
        @else
          <div class="alert alert-info">Tambahkan minimal satu artikel untuk melihat pratinjau.</div>
        @endif
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">Ringkasan Artikel</div>
          <div class="card-body p-0">
            @if($articles->isEmpty())
              <p class="text-muted p-3 mb-0">Belum ada artikel yang ditambahkan.</p>
            @else
              <div class="table-responsive">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Judul</th>
                      <th>Slug</th>
                      <th>Penulis</th>
                      <th>Tanggal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($articles as $item)
                      <tr>
                        <td>{{ $item['title'] ?? '-' }}</td>
                        <td>{{ $item['slug'] ?? '-' }}</td>
                        <td>{{ $item['author'] ?? '-' }}</td>
                        <td>{{ $item['date'] ?? '-' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
  @include('admin.pages.partials.section-editor-script')
@endsection
