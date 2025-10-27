@php
    $themeName = $theme ?? 'theme-restoran';
    \Illuminate\Support\Facades\View::addNamespace('themeRestoran', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeRestoran::components.gallery.page', ['theme' => $themeName, 'categories' => $categories ?? null, 'items' => $items ?? null])
