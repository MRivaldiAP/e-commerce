@php
    $themeName = $theme ?? 'theme-restoran';
    \Illuminate\Support\Facades\View::addNamespace('themeRestoran', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeRestoran::components.article-detail.page', [
    'theme' => $themeName,
    'settings' => $settings ?? [],
    'listSettings' => $listSettings ?? [],
    'article' => $article ?? [],
    'recommended' => $recommended ?? [],
    'meta' => $meta ?? [],
])
