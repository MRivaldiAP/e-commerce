@php
    $themeName = $theme ?? 'theme-restoran';
    \Illuminate\Support\Facades\View::addNamespace('themeRestoran', base_path('themes/' . $themeName . '/views'));
@endphp

@include('themeRestoran::components.article.page', ['theme' => $themeName, 'settings' => $settings ?? [], 'meta' => $meta ?? [], 'articles' => $articles ?? [], 'timeline' => $timeline ?? [], 'filters' => $filters ?? []])
