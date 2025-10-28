<article class="card border-0 shadow-sm">
    @if(!empty($article['image']))
        <img src="{{ $article['image'] }}" class="card-img-top" alt="{{ $article['title'] ?? 'Artikel' }}">
    @endif
    <div class="card-body p-4">
        <div class="d-flex align-items-center text-muted mb-3 small">
            @if($article['meta']['show_date'] ?? false)
                <span class="me-3"><i class="far fa-calendar-alt me-1"></i>{{ $article['meta']['date'] ?? '' }}</span>
            @endif
            @if($article['meta']['show_author'] ?? false)
                <span><i class="far fa-user me-1"></i>{{ $article['meta']['author'] ?? '' }}</span>
            @endif
        </div>
        <h1 class="mb-4 h3">{{ $article['title'] ?? 'Artikel' }}</h1>
        <div class="article-content">
            {!! $article['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
        </div>
    </div>
</article>

@include('themeRestoran::components.article-detail.sections.comments', ['comments' => $comments])
