@if(($settings['comments.visible'] ?? '1') == '1')
    <h2>{{ $settings['comments.heading'] ?? 'Komentar' }}</h2>
    <p class="text-muted">Fitur komentar akan tersedia segera.</p>
@elseif(!empty($settings['comments.disabled_text']))
    <div class="article-detail__notice">{{ $settings['comments.disabled_text'] }}</div>
@endif
