@php
    use App\Support\ThemeMedia;

    $image = ThemeMedia::url($article['image'] ?? null);
    $showMeta = $showMetaSection ?? true;
@endphp
<div class="blog__details__text">
    @if($image)
        <img src="{{ $image }}" alt="{{ $article['title'] ?? 'Artikel' }}" class="img-fluid mb-4">
    @endif
    <h2>{{ $article['title'] ?? 'Artikel' }}</h2>
    @if($showMeta)
        <ul class="blog__details__author">
            @if(($settings['meta.show_author'] ?? '1') === '1' && !empty($article['author']))
                <li><i class="fa fa-user"></i> {{ $article['author'] }}</li>
            @endif
            @if(($settings['meta.show_date'] ?? '1') === '1' && !empty($dateFormatted))
                <li><i class="fa fa-calendar-o"></i> {{ $dateFormatted }}</li>
            @endif
        </ul>
    @endif
    <div class="blog__details__content">
        {!! $article['content'] ?? '<p>Konten artikel belum tersedia.</p>' !!}
    </div>
</div>
@once
    <style>
        .blog__details__author { list-style: none; padding: 0; margin: 1rem 0; display: flex; gap: 1.5rem; }
        .blog__details__author li { color: #6c757d; font-size: 0.95rem; }
        .blog__details__author i { margin-right: 0.5rem; }
        .blog__details__content p { margin-bottom: 1.5rem; line-height: 1.7; }
    </style>
@endonce
