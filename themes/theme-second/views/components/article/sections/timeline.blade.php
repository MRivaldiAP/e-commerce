<div id="timeline" class="blog__sidebar__item">
    <h4>{{ $settings['timeline.heading'] ?? 'Arsip Artikel' }}</h4>
    @if(collect($timeline)->isEmpty())
        <p class="text-muted mb-0">Belum ada arsip.</p>
    @else
        <div class="blog__sidebar__item__categories">
            <ul>
                @foreach($timeline as $year => $months)
                    <li>
                        <span class="d-block fw-bold">{{ $year }}</span>
                        <ul class="list-unstyled ms-3 mt-2">
                            @foreach($months as $monthKey => $monthData)
                                <li class="mb-2">
                                    <a href="{{ route('articles.index', ['year' => $year, 'month' => $monthKey, 'search' => $filters['search'] ?? null]) }}" class="d-block">
                                        {{ $monthData['name'] ?? $monthKey }} ({{ $monthData['articles']->count() }})
                                    </a>
                                    <ul class="list-unstyled ms-3 mt-1">
                                        @foreach($monthData['articles'] as $item)
                                            <li><a href="{{ route('articles.show', ['slug' => $item['slug']]) }}">{{ $item['title'] ?? 'Artikel' }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@once
    <style>
        .blog__sidebar__item__categories > ul > li { margin-bottom: 1.5rem; }
        .blog__sidebar__item__categories ul ul li { font-size: 0.95rem; }
    </style>
@endonce
