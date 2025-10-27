@php
    $themeName = $theme ?? 'theme-second';
    \Illuminate\Support\Facades\View::addNamespace('themeSecond', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeSecond::components.contact.page', ['theme' => $themeName])
