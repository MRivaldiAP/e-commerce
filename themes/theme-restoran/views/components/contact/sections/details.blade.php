@php
    $detailItems = $detailItems ?? collect(json_decode($settings['details.items'] ?? '[]', true))
        ->filter(static function ($item) {
            return is_array($item);
        })
        ->values()
        ->all();
@endphp
<div id="details" class="container-xxl py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">{{ $settings['details.heading'] ?? 'Hubungi Kami' }}</h5>
            @if(!empty($settings['details.description']))
            <p class="text-muted">{{ $settings['details.description'] }}</p>
            @endif
        </div>
        <div class="row g-4 justify-content-center">
            @forelse($detailItems as $item)
            @php $formatLink = function (?string $value, ?string $link) {
                if (! $value) {
                    return '';
                }
                if ($link) {
                    return '<a href="' . e($link) . '" target="_blank" rel="noopener" class="text-decoration-none text-white-50">' . e($value) . '</a>';
                }
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return '<a href="mailto:' . e($value) . '" class="text-decoration-none text-white-50">' . e($value) . '</a>';
                }
                if (\Illuminate\Support\Str::startsWith($value, '+') || preg_match('/^[0-9\s()+-]+$/', $value)) {
                    return '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $value)) . '" class="text-decoration-none text-white-50">' . e($value) . '</a>';
                }
                return e($value);
            }; @endphp
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="contact-card bg-dark text-white text-center rounded py-4 px-3 h-100">
                    @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }} mb-3" aria-hidden="true"></i>
                    @else
                    <i class="fa fa-map-marker-alt mb-3" aria-hidden="true"></i>
                    @endif
                    @if(!empty($item['label']))
                    <h5 class="text-uppercase text-primary mb-2">{{ $item['label'] }}</h5>
                    @endif
                    @if(!empty($item['value']))
                    <p class="mb-0 text-white-50">{!! $formatLink($item['value'], $item['link'] ?? null) !!}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p class="text-muted">Informasi kontak belum tersedia.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
