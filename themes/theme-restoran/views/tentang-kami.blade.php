@php
    $themeName = $theme ?? 'theme-restoran';
    \Illuminate\Support\Facades\View::addNamespace('themeRestoran', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeRestoran::components.about.page', ['theme' => $themeName])
