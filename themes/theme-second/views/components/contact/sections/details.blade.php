@php
    use Illuminate\Support\Str;

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
<section id="details" class="contact spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title contact__title">
                    <h2>{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h2>
                    @if(!empty($settings['details.description']))
                        <p>{{ $settings['details.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            @forelse($detailItems as $item)
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="contact__widget__item">
                        @if(!empty($item['icon']))
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        @endif
                        @if(!empty($item['label']))
                            <h4>{{ $item['label'] }}</h4>
                        @endif
                        @if(!empty($item['value']))
                            <p>{!! $formatLink($item['value'], $item['link'] ?? null) !!}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-lg-12">
                    <p class="text-center">Informasi kontak akan segera diperbarui.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
