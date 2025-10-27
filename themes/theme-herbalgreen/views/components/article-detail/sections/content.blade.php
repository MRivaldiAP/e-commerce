@php
    $image = \App\Support\ThemeMedia::url($article['image'] ?? null);
    $showMeta = $showMetaSection ?? true;
    $showComments = $showCommentsSection ?? true;
    $showRecommendations = $showRecommendationsSection ?? true;
    $hasCommentsContent = $showComments && trim($commentsView ?? '') !== '';
    $hasRecommendationsContent = $showRecommendations && trim($recommendationsView ?? '') !== '';
@endphp
<section id="content" class="article-detail">
    <article class="article-detail__main">
        @if($image)
            <div class="article-detail__media" style="background-image:url('{{ $image }}')"></div>
        @endif
        <div class="article-detail__body">
            @if($showMeta)
                <div class="article-detail__meta">
                    @if(($settings['meta.show_date'] ?? '1') == '1' && !empty($dateFormatted))
                        <span>{{ $dateFormatted }}</span>
                    @endif
                    @if(($settings['meta.show_author'] ?? '1') == '1' && !empty($article['author']))
                        <span>• {{ $article['author'] }}</span>
                    @endif
                </div>
            @endif
            <h1>{{ $article['title'] ?? 'Artikel' }}</h1>
            <div class="article-detail__content">
                {!! $article['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
            </div>
        </div>
        @if($hasCommentsContent)
            <div id="comments" class="article-detail__comments">
                {!! $commentsView !!}
            </div>
        @endif
    </article>
    @if($hasRecommendationsContent)
        <aside id="recommendations" class="article-detail__sidebar">
            {!! $recommendationsView !!}
        </aside>
    @endif
</section>
