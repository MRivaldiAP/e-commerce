@php
    $visible = $advantages['visible'] ?? false;
    $heading = $advantages['heading'] ?? 'Keunggulan Kami';
    $description = $advantages['description'] ?? null;
    $items = is_array($advantages['items'] ?? null) ? $advantages['items'] : [];
@endphp
@if($visible)
<div id="advantages" class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">{{ $heading }}</h5>
            @if(!empty($description))
            <p class="text-muted">{{ $description }}</p>
            @endif
        </div>
        <div class="row g-4">
            @foreach($items as $item)
            <div class="col-lg-4 col-md-6">
                <div class="service-item rounded pt-3 h-100">
                    <div class="p-4">
                        @if(!empty($item['icon']))
                        <i class="{{ $item['icon'] }} text-primary fa-3x mb-3"></i>
                        @endif
                        <h5>{{ $item['title'] ?? '' }}</h5>
                        <p class="mb-0">{{ $item['text'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
