@php
    $visible = $hero['visible'] ?? false;
    $classes = $hero['classes'] ?? 'container-xxl py-5 hero-header mb-5';
    $style = $hero['style'] ?? '';
    $heading = $hero['heading'] ?? 'Tentang Kami';
    $text = $hero['text'] ?? null;
@endphp
@if($visible)
<div id="hero" class="{{ $classes }}" style="{{ $style }}">
    <div class="container text-center my-5 pt-5 pb-4">
        <h1 class="display-4 text-white mb-3">{{ $heading }}</h1>
        @if(!empty($text))
        <p class="text-white-50 mb-0">{{ $text }}</p>
        @endif
    </div>
</div>
@endif
