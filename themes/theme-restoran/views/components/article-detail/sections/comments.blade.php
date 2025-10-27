<section id="comments" class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        @if($comments['visible'] ?? false)
            <h4 class="mb-3">{{ $comments['heading'] ?? 'Komentar' }}</h4>
            <p class="text-muted mb-0">Fitur komentar akan segera tersedia.</p>
        @else
            <div class="alert alert-light border mb-0">{{ $comments['disabled_text'] ?? 'Komentar dinonaktifkan.' }}</div>
        @endif
    </div>
</section>
