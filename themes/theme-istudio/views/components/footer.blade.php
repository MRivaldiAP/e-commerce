@php
    $footer = $footer ?? [];
    $brand = $brand ?? ['label' => 'iSTUDIO', 'logo' => null, 'url' => url('/')];
    $links = $footer['links'] ?? [];
    $showHotlinks = $footer['show_hotlinks'] ?? false;
    $address = $footer['address'] ?? ['visible' => false, 'text' => ''];
    $phone = $footer['phone'] ?? ['visible' => false, 'text' => ''];
    $email = $footer['email'] ?? ['visible' => false, 'text' => ''];
    $social = $footer['social'] ?? ['visible' => false, 'text' => ''];
    $schedule = $footer['schedule'] ?? ['visible' => false, 'text' => ''];
    $copyright = $footer['copyright'] ?? ('© ' . date('Y') . ' ' . ($brand['label'] ?? 'Storefront'));
    $description = $footer['description'] ?? 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum et tempor sit. Aliqu diam amet diam et eos labore.';

    $socialLinks = [];
    if ($social['visible'] ?? false) {
        $socialText = trim((string) ($social['text'] ?? ''));
        if ($socialText !== '') {
            foreach (preg_split('/\s+/', $socialText) as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $socialLinks[] = $url;
                }
            }
        }
    }
@endphp
<div id="footer" class="container-fluid bg-dark text-white-50 footer pt-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-3">
                <a href="{{ $brand['url'] ?? url('/') }}" class="d-inline-flex align-items-center mb-3 text-decoration-none">
                    @if (!empty($brand['logo']))
                        <img src="{{ $brand['logo'] }}" alt="{{ $brand['label'] ?? 'Brand' }}" style="max-height: 48px; width: auto;">
                    @else
                        <h1 class="text-white mb-0">{{ $brand['label'] ?? 'Brand' }}</h1>
                    @endif
                </a>
                <p class="mb-0">{{ $description }}</p>
            </div>
            <div class="col-md-6 col-lg-3">
                <h5 class="text-white mb-4">Hubungi Kami</h5>
                <ul class="list-unstyled mb-3">
                    @if ($address['visible'] ?? false)
                        <li class="mb-2"><i class="fa fa-map-marker-alt me-2"></i>{{ $address['text'] }}</li>
                    @endif
                    @if ($phone['visible'] ?? false)
                        <li class="mb-2"><i class="fa fa-phone-alt me-2"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone['text']) }}" class="text-white-50 text-decoration-none">{{ $phone['text'] }}</a></li>
                    @endif
                    @if ($email['visible'] ?? false)
                        <li class="mb-2"><i class="fa fa-envelope me-2"></i><a href="mailto:{{ $email['text'] }}" class="text-white-50 text-decoration-none">{{ $email['text'] }}</a></li>
                    @endif
                </ul>
                @if ($socialLinks !== [])
                    <div class="d-flex pt-2 flex-wrap gap-2">
                        @foreach ($socialLinks as $link)
                            <a class="btn btn-outline-primary btn-square border-2" href="{{ $link }}" target="_blank" rel="noopener">
                                <i class="fab fa-instagram"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-md-6 col-lg-3">
                @if ($showHotlinks && count($links))
                    <h5 class="text-white mb-4">Tautan Populer</h5>
                    @foreach ($links as $link)
                        <a class="btn btn-link" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                    @endforeach
                @else
                    <h5 class="text-white mb-4">Jam Operasional</h5>
                    @if ($schedule['visible'] ?? false)
                        <p class="mb-0">{{ $schedule['text'] }}</p>
                    @else
                        <p class="mb-0">Senin - Jumat: 09.00 - 18.00</p>
                    @endif
                @endif
            </div>
            <div class="col-md-6 col-lg-3">
                <h5 class="text-white mb-4">Tetap Terhubung</h5>
                <p class="mb-4">Daftarkan email Anda untuk mendapatkan promo terbaru.</p>
                <form class="position-relative">
                    <input type="email" class="form-control border-0 w-100 ps-4 pe-5" placeholder="Email Anda" required>
                    <button type="submit" class="btn shadow-none position-absolute top-0 end-0 mt-2 me-2"><i class="fa fa-paper-plane text-primary fs-5"></i></button>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="copyright border-top border-secondary pt-4 mt-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    {{ $copyright }}
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="footer-menu d-inline-flex gap-3">
                        @foreach (array_slice($links, 0, 4) as $link)
                            <a href="{{ $link['href'] }}" class="text-white-50 text-decoration-none">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
