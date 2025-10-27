@php
    $themeName = $theme ?? 'theme-second';
    \Illuminate\Support\Facades\View::addNamespace('themeSecond', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeSecond::components.product.page', ['theme' => $themeName])
