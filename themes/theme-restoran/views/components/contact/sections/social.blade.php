<div id="social" class="container-xxl py-5 bg-dark">
    <div class="container text-center">
        <h5 class="section-title ff-secondary text-primary fw-normal">{{ $settings['social.heading'] ?? 'Ikuti Kami' }}</h5>
        @if(!empty($settings['social.description']))
        <p class="text-white-50 mb-4">{{ $settings['social.description'] }}</p>
        @endif
        <div class="contact-social">
            @forelse($socialItems as $item)
                @php $url = $item['url'] ?? '#'; @endphp
                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-white">
                    @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }}"></i>
                    @else
                    <i class="fa fa-link"></i>
                    @endif
                </a>
            @empty
                <span class="text-white-50">Belum ada tautan media sosial yang ditampilkan.</span>
            @endforelse
        </div>
    </div>
</div>
