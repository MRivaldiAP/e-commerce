<div class="sidebar-card">
    <h3>{{ $settings['recommendations.heading'] ?? 'Artikel Lainnya' }}</h3>
    <ul class="sidebar-list">
        @foreach($recommended as $item)
            <li>
                <a href="{{ route('articles.show', ['slug' => $item['slug']]) }}">
                    <span class="title">{{ $item['title'] ?? 'Artikel' }}</span>
                    <span class="date">{{ $item['date_formatted'] ?? '' }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
