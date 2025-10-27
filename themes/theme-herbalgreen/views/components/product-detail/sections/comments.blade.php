<section id="comments" class="section">
    <style>
        #comments { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        #comments .comment { padding: 1.5rem; border-bottom: 1px solid #e0f2f1; }
        #comments .comment:last-child { border-bottom: none; }
        #comments .comment strong { display: block; margin-bottom: 0.5rem; }
    </style>
    <h2>{{ $settings['comments.heading'] ?? 'Komentar Produk' }}</h2>
    @if($comments->isEmpty())
        <p class="text-center">Belum ada komentar.</p>
    @else
        @foreach($comments as $comment)
            <div class="comment">
                <strong>{{ $comment->user?->name ?? $comment->name ?? 'Pengguna' }}</strong>
                <small>{{ optional($comment->created_at)->format('d M Y') }}</small>
                <p>{{ $comment->content }}</p>
            </div>
        @endforeach
    @endif
</section>
