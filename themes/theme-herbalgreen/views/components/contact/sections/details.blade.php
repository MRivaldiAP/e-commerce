@php
    use Illuminate\Support\Str;

    $detailItems = $detailItems ?? collect(json_decode($settings['details.items'] ?? '[]', true))
        ->filter(static function ($item) {
            return is_array($item);
        })
        ->values()
        ->all();

    $formatLink = function (?string $value, ?string $link) {
        if (! $value) {
            return '';
        }
        if ($link) {
            return '<a href="' . e($link) . '" target="_blank" rel="noopener">' . e($value) . '</a>';
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '<a href="mailto:' . e($value) . '">' . e($value) . '</a>';
        }
        if (Str::startsWith($value, '+') || preg_match('/^[0-9\s()+-]+$/', $value)) {
            return '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $value)) . '">' . e($value) . '</a>';
        }
        return e($value);
    };
@endphp
<section id="details" class="contact-details">
    <div class="contact-details__header">
        <h2>{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h2>
        @if(!empty($settings['details.description']))
            <p>{{ $settings['details.description'] }}</p>
        @endif
    </div>
    <div class="contact-cards">
        @forelse($detailItems as $item)
            <article class="contact-card">
                @if(!empty($item['icon']))
                    <span class="contact-card__icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                @endif
                @if(!empty($item['label']))
                    <span class="contact-card__label">{{ $item['label'] }}</span>
                @endif
                @if(!empty($item['value']))
                    <p class="contact-card__value">{!! $formatLink($item['value'], $item['link'] ?? null) !!}</p>
                @endif
            </article>
        @empty
            <p class="text-center">Informasi kontak akan segera hadir.</p>
        @endforelse
    </div>
</section>
