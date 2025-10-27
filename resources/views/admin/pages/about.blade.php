@extends('layout.admin')

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
        'updateRoute' => route('admin.pages.about.update'),
      ])
      <div class="col-md-8 position-sticky" style="top:0;height:100vh">
        <iframe id="page-preview" src="{{ $previewUrl }}" class="w-100 border h-100"></iframe>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
  @include('admin.pages.partials.section-editor-script')
@endsection
