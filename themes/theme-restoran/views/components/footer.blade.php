@php
    $footer = $footer ?? [];
    $links = $footer['links'] ?? [];
    $showHotlinks = $footer['show_hotlinks'] ?? false;
    $address = $footer['address'] ?? ['visible' => false, 'text' => ''];
    $phone = $footer['phone'] ?? ['visible' => false, 'text' => ''];
    $email = $footer['email'] ?? ['visible' => false, 'text' => ''];
    $social = $footer['social'] ?? ['visible' => false, 'text' => ''];
    $schedule = $footer['schedule'] ?? ['visible' => false, 'text' => ''];
    $copyright = $footer['copyright'] ?? '';
@endphp
<div id="footer" class="container-fluid bg-dark text-light footer pt-5 mt-5">
    <div class="container py-5">
        <div class="row g-5">
            @if ($showHotlinks && count($links))
                <div class="col-lg-3 col-md-6">
                    <h4 class="section-title ff-secondary text-start text-primary fw-normal mb-4">Hot Links</h4>
                    @foreach ($links as $link)
                        <a class="btn btn-link" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            @endif
            @if (($address['visible'] ?? false) || ($phone['visible'] ?? false) || ($email['visible'] ?? false) || ($social['visible'] ?? false))
                <div class="col-lg-4 col-md-6">
                    <h4 class="section-title ff-secondary text-start text-primary fw-normal mb-4">Contact</h4>
                    @if ($address['visible'] ?? false)
                        <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>{{ $address['text'] }}</p>
                    @endif
                    @if ($phone['visible'] ?? false)
                        <p class="mb-2"><i class="fa fa-phone-alt me-3"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone['text']) }}" class="text-light text-decoration-none">{{ $phone['text'] }}</a></p>
                    @endif
                    @if ($email['visible'] ?? false)
                        <p class="mb-2"><i class="fa fa-envelope me-3"></i><a href="mailto:{{ $email['text'] }}" class="text-light text-decoration-none">{{ $email['text'] }}</a></p>
                    @endif
                    @if ($social['visible'] ?? false)
                        <div class="d-flex pt-2">
                            <a class="btn btn-outline-light btn-social" href="{{ $social['text'] }}" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        </div>
                    @endif
                </div>
            @endif
            @if ($schedule['visible'] ?? false)
                <div class="col-lg-3 col-md-6">
                    <h4 class="section-title ff-secondary text-start text-primary fw-normal mb-4">Opening</h4>
                    <p class="mb-2">{{ $schedule['text'] }}</p>
                </div>
            @endif
        </div>
    </div>
    <div class="container">
        <div class="copyright">
            <div class="row">
                <div class="col-12 text-center">
                    @if (!empty($copyright))
                        <span>{{ $copyright }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
