@php
    $themeName = $theme ?? 'theme-herbalgreen';
    \Illuminate\Support\Facades\View::addNamespace('themeHerbalGreen', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeHerbalGreen::components.gallery.page', array_merge([
    'theme' => $themeName,
], get_defined_vars()))
