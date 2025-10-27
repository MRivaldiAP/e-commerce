<div id="recommendations" class="blog__sidebar">
    <div class="blog__sidebar__item">
        <h4>{{ $settings['recommendations.heading'] ?? 'Artikel Lainnya' }}</h4>
        <div class="blog__sidebar__recent">
            @foreach($recommended as $item)
                <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}" class="blog__sidebar__recent__item">
                    <h6>{{ $item['title'] ?? 'Artikel' }}</h6>
                    <span>{{ $item['date_formatted'] ?? '' }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
