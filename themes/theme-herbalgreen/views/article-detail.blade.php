@php
    $themeName = $theme ?? 'theme-herbalgreen';
    \Illuminate\Support\Facades\View::addNamespace('themeHerbalGreen', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeHerbalGreen::components.article-detail.page', array_merge([
    'theme' => $themeName,
], get_defined_vars()))
