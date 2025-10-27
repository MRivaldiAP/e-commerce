@php
    $commentsEnabled = ($showCommentsSection ?? true) && ($settings['comments.visible'] ?? '1') === '1';
@endphp
<div id="comments" class="blog__details__comment mt-5">
    @if($commentsEnabled)
        <h4>{{ $settings['comments.heading'] ?? 'Komentar' }}</h4>
        <p class="text-muted">Fitur komentar akan segera hadir.</p>
    @elseif($showCommentsSection ?? true)
        <div class="alert alert-light border">{{ $settings['comments.disabled_text'] ?? 'Komentar dinonaktifkan.' }}</div>
    @endif
</div>
