<div id="comments" class="blog__details__comment mt-5">
    @if(($settings['comments.visible'] ?? '1') === '1')
        <h4>{{ $settings['comments.heading'] ?? 'Komentar' }}</h4>
        <p class="text-muted">Fitur komentar akan segera hadir.</p>
    @else
        <div class="alert alert-light border">{{ $settings['comments.disabled_text'] ?? 'Komentar dinonaktifkan.' }}</div>
    @endif
</div>
