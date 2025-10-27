@php
    $visible = $quote['visible'] ?? false;
    $text = $quote['text'] ?? null;
    $author = $quote['author'] ?? null;
@endphp
@if($visible)
<div id="quote" class="container-xxl py-5 bg-dark">
    <div class="container text-center">
        <div class="mx-auto" style="max-width:720px;">
            <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
            <p class="fs-4 text-white-50">“{{ $text }}”</p>
            @if(!empty($author))
            <h5 class="text-white mb-0">{{ $author }}</h5>
            @endif
        </div>
    </div>
</div>
@endif
