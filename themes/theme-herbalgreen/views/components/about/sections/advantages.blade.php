@php
    $advantages = $advantages ?? collect(json_decode($settings['advantages.items'] ?? '[]', true))
        ->filter(static function ($item) {
            return is_array($item);
        })
        ->values()
        ->all();
@endphp
<section id="advantages" class="about-advantages">
    <div class="section-header">
        <h2>{{ $settings['advantages.heading'] ?? 'Keunggulan Kami' }}</h2>
        @if(!empty($settings['advantages.description']))
            <p>{{ $settings['advantages.description'] }}</p>
        @endif
    </div>
    <div class="about-advantages__grid">
        @foreach($advantages as $advantage)
            <div class="about-advantages__item">
                @if(!empty($advantage['icon']))
                    <span class="about-advantages__icon"><i class="{{ $advantage['icon'] }}"></i></span>
                @endif
                <h3>{{ $advantage['title'] ?? '' }}</h3>
                <p>{{ $advantage['text'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</section>
