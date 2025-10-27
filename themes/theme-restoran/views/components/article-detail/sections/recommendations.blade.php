@if($recommendations['visible'] ?? false)
<div id="recommendations" class="card border-0 shadow-sm">
    <div class="card-body">
        <h4 class="card-title">{{ $recommendations['heading'] ?? 'Artikel Lainnya' }}</h4>
        <ul class="list-unstyled mb-0">
            @foreach($recommendations['items'] ?? [] as $item)
                <li class="mb-3">
                <a href="{{ $item['url'] ?? '#' }}" class="text-decoration-none">
                    <span class="d-block fw-semibold">{{ $item['title'] ?? 'Artikel' }}</span>
                    @if(!empty($item['date']))
                        <small class="text-muted">{{ $item['date'] }}</small>
                    @endif
                </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endif
