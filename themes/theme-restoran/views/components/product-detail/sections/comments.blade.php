@if($comments['visible'] ?? false)
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h3 class="mb-4">{{ $comments['heading'] ?? 'Komentar Pelanggan' }}</h3>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @forelse($comments['items'] ?? [] as $comment)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">{{ $comment['author'] ?? 'Pengguna' }}</h5>
                            <small class="text-muted">{{ $comment['date'] ?? '' }}</small>
                        </div>
                        <p class="card-text mb-0">{{ $comment['content'] ?? '' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted mb-0">Belum ada komentar untuk produk ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endif
