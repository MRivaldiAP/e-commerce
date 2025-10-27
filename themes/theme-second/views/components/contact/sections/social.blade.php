@php
    $visibleSocials = collect($socialItems)->filter(function ($item) {
        return ($item['visible'] ?? '1') !== '0';
    });
@endphp
<section id="social" class="contact spad" style="padding-top:0">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-title contact__title">
                    <h2>{{ $settings['social.heading'] ?? 'Ikuti Kami' }}</h2>
                    @if(!empty($settings['social.description']))
                        <p>{{ $settings['social.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="contact__social text-center">
                    @forelse($visibleSocials as $item)
                        @php $url = $item['url'] ?? '#'; @endphp
                        <a href="{{ $url }}" target="_blank" rel="noopener">
                            @if(!empty($item['icon']))
                                <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            @else
                                <i class="fa fa-link" aria-hidden="true"></i>
                            @endif
                        </a>
                    @empty
                        <span>Belum ada tautan media sosial yang ditampilkan.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
