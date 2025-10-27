@php
    $themeName = $theme ?? 'theme-restoran';
    \Illuminate\Support\Facades\View::addNamespace('themeRestoran', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeRestoran::components.product-detail.page', ['theme' => $themeName, 'product' => $product])
