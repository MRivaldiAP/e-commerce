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
        'updateRoute' => route('admin.pages.article-detail.update'),
      ])
      <div class="col-md-8 position-sticky" style="top:0;height:100vh">
        @if($previewUrl)
          <iframe id="page-preview" src="{{ $previewUrl }}" class="w-100 border h-100"></iframe>
        @else
          <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="alert alert-info mb-0">Tambahkan artikel pada halaman artikel untuk melihat pratinjau detail.</div>
          </div>
        @endif
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">Daftar Artikel Tersedia</div>
          <div class="card-body p-0">
            @if($articles->isEmpty())
              <p class="text-muted p-3 mb-0">Belum ada artikel yang dapat dipratinjau.</p>
            @else
              <div class="table-responsive">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Judul</th>
                      <th>Slug</th>
                      <th>Pratinjau</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($articles as $item)
                      <tr>
                        <td>{{ $item['title'] ?? '-' }}</td>
                        <td>{{ $item['slug'] ?? '-' }}</td>
                        <td>
                          @if(!empty($item['slug']))
                            <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" target="_blank" rel="noopener">Lihat</a>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
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
