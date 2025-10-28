@php
    $articleImage = function (?string $path) use ($theme) {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, ['http://', 'https://'])) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    };
@endphp
<section id="list" class="blog spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8">
                @forelse($articles as $article)
                    @php $image = $articleImage($article['image'] ?? null); @endphp
                    <div class="blog__item">
                        <div class="blog__item__pic">
                            <img src="{{ $image ?? asset('storage/themes/' . $theme . '/img/blog/blog-1.jpg') }}" alt="{{ $article['title'] ?? 'Artikel' }}">
                        </div>
                        <div class="blog__item__text">
                            <ul>
                                @if(!empty($article['author']))
                                    <li><i class="fa fa-user"></i> {{ $article['author'] }}</li>
                                @endif
                                @if(!empty($article['date_formatted']))
                                    <li><i class="fa fa-calendar-o"></i> {{ $article['date_formatted'] }}</li>
                                @endif
                            </ul>
                            <h5><a href="{{ route('articles.show', ['slug' => $article['slug']]) }}">{{ $article['title'] ?? 'Tanpa Judul' }}</a></h5>
                            @if(!empty($article['excerpt']))
                                <p>{{ $article['excerpt'] }}</p>
                            @endif
                            <a href="{{ route('articles.show', ['slug' => $article['slug']]) }}" class="blog__btn">{{ $buttonLabel }} <span class="arrow_right"></span></a>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border">{{ $emptyText }}</div>
                @endforelse
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="blog__sidebar">
                    <div id="search" class="blog__sidebar__search">
                        <form method="GET">
                            <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
                            <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
                            <input type="text" name="search" placeholder="{{ $searchPlaceholder }}" value="{{ $filters['search'] ?? '' }}">
                            <button type="submit"><span class="icon_search"></span></button>
                        </form>
                    </div>
                    @if(!empty($filters['year']) || !empty($filters['month']) || !empty($filters['search']))
                        <div class="blog__sidebar__item">
                            <a href="{{ route('articles.index') }}" class="site-btn w-100 text-center">Reset Filter</a>
                        </div>
                    @endif
                    @includeWhen(($settings['timeline.visible'] ?? '1') === '1', 'themeSecond::components.article.sections.timeline', [
                        'settings' => $settings,
                        'timeline' => $timeline,
                        'filters' => $filters,
                    ])
                </div>
            </div>
        </div>
    </div>
</section>
