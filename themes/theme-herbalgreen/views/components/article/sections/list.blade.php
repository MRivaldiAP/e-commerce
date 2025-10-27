<section id="list" class="article-layout">
    <div class="article-layout__content">
        @forelse($articles as $article)
            @php $image = $articleImage($article['image'] ?? null); @endphp
            <article class="article-card">
                @if($image)
                    <div class="article-card__media" style="background-image:url('{{ $image }}')"></div>
                @endif
                <div class="article-card__body">
                    <div class="article-card__meta">
                        @if(!empty($article['date_formatted']))
                            <span>{{ $article['date_formatted'] }}</span>
                        @endif
                        @if(!empty($article['author']))
                            <span>• {{ $article['author'] }}</span>
                        @endif
                    </div>
                    <h2>{{ $article['title'] ?? 'Artikel' }}</h2>
                    @if(!empty($article['excerpt']))
                        <p>{{ $article['excerpt'] }}</p>
                    @endif
                    <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="article-card__link">{{ $buttonLabel }}</a>
                </div>
            </article>
        @empty
            <div class="article-empty">{{ $emptyText }}</div>
        @endforelse
    </div>
    <aside class="article-layout__sidebar">
        <div id="search" class="sidebar-card">
            <h3>Cari Artikel</h3>
            <form method="GET" class="sidebar-search">
                <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
                <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
                <input type="text" name="search" placeholder="{{ $searchPlaceholder }}" value="{{ $filters['search'] ?? '' }}">
                <button type="submit">Cari</button>
            </form>
            @if(!empty($filters['search']) || !empty($filters['year']) || !empty($filters['month']))
                <a href="{{ route('articles.index') }}" class="sidebar-reset">Reset Filter</a>
            @endif
        </div>
        @if($timelineVisible)
            <div id="timeline" class="sidebar-card">
                <h3>{{ $timelineHeading }}</h3>
                @if($timeline->isEmpty())
                    <p class="text-muted">Belum ada arsip.</p>
                @else
                    <div class="timeline">
                        @foreach($timeline as $year => $months)
                            <div class="timeline__year">
                                <h4>{{ $year }}</h4>
                                <ul>
                                    @foreach($months as $monthKey => $monthData)
                                        <li>
                                            <a href="{{ route('articles.index', ['year' => $year, 'month' => $monthKey, 'search' => $filters['search'] ?? null]) }}">{{ $monthData['name'] ?? $monthKey }} ({{ $monthData['articles']->count() }})</a>
                                            <ul>
                                                @foreach($monthData['articles'] as $item)
                                                    <li><a href="{{ route('articles.show', ['slug' => $item['slug']]) }}">{{ $item['title'] ?? 'Artikel' }}</a></li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </aside>
</section>
