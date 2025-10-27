@php
    $articles = collect($list['articles'] ?? []);
    $search = $list['search'] ?? [];
    $filters = $search['filters'] ?? [];
@endphp
<div id="list" class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            @if($articles->isEmpty())
                <div class="alert alert-light border">{{ $list['empty_text'] ?? 'Belum ada artikel tersedia.' }}</div>
            @else
                @foreach($articles as $article)
                    <div class="card border-0 shadow-sm mb-4">
                        @if(!empty($article['image']))
                            <img src="{{ $article['image'] }}" class="card-img-top" alt="{{ $article['title'] ?? 'Artikel' }}">
                        @endif
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center text-muted mb-2 small">
                                @if(!empty($article['date']))
                                    <span class="me-3"><i class="far fa-calendar-alt me-1"></i>{{ $article['date'] }}</span>
                                @endif
                                @if(!empty($article['author']))
                                    <span><i class="far fa-user me-1"></i>{{ $article['author'] }}</span>
                                @endif
                            </div>
                            <h3 class="card-title h4">{{ $article['title'] ?? 'Artikel' }}</h3>
                            @if(!empty($article['excerpt']))
                                <p class="card-text">{{ $article['excerpt'] }}</p>
                            @endif
                            <a href="{{ $article['url'] ?? '#' }}" class="btn btn-sm btn-primary">{{ $list['button_label'] ?? 'Baca Selengkapnya' }}</a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="col-lg-4">
            <div id="search" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title">Cari Artikel</h4>
                    <form method="GET" class="d-flex flex-column gap-3">
                        <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
                        <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
                        <div class="form-group">
                            <label for="search-input" class="form-label">Pencarian</label>
                            <input type="text" class="form-control" id="search-input" name="search" placeholder="{{ $search['placeholder'] ?? 'Cari artikel...' }}" value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </form>
                </div>
            </div>
            @if($timeline['visible'] ?? false)
                <div id="timeline" class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">{{ $timeline['heading'] ?? 'Arsip Artikel' }}</h4>
                        <ul class="list-unstyled mb-0">
                            @foreach($timeline['items'] ?? [] as $item)
                                <li class="mb-2">
                                    <a href="{{ $item['url'] ?? '#' }}" class="text-decoration-none">
                                        <span class="d-block fw-semibold">{{ $item['label'] ?? '' }}</span>
                                        <small class="text-muted">{{ $item['count'] ?? 0 }} Artikel</small>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
