<section id="social" class="contact-social">
    <div class="contact-details__header">
        <h2>{{ $settings['social.heading'] ?? 'Terhubung dengan Kami' }}</h2>
        @if(!empty($settings['social.description']))
            <p>{{ $settings['social.description'] }}</p>
        @endif
    </div>
    <div class="contact-social__list">
        @forelse($socialItems as $item)
            @php $url = $item['url'] ?? '#'; @endphp
            <a class="contact-social__item" href="{{ $url }}" target="_blank" rel="noopener">
                @if(!empty($item['icon']))
                    <span><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                @endif
                <span>{{ $item['label'] ?? $url }}</span>
            </a>
        @empty
            <span>Belum ada tautan media sosial yang ditampilkan.</span>
        @endforelse
    </div>
</section>
