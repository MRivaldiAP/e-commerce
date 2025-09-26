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
<footer id="footer" class="footer spad">
    <div class="container">
        <div class="row">
            @if (($address['visible'] ?? false) || ($phone['visible'] ?? false) || ($email['visible'] ?? false))
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="footer__about">
                        <h6 class="text-uppercase mb-3">Hubungi Kami</h6>
                        <ul>
                            @if ($address['visible'] ?? false)
                                <li>Alamat: {{ $address['text'] }}</li>
                            @endif
                            @if ($phone['visible'] ?? false)
                                <li>Telepon: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone['text']) }}">{{ $phone['text'] }}</a></li>
                            @endif
                            @if ($email['visible'] ?? false)
                                <li>Email: <a href="mailto:{{ $email['text'] }}">{{ $email['text'] }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif
            @if ($showHotlinks && count($links))
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="footer__widget">
                        <h6>Hot Links</h6>
                        <ul>
                            @foreach ($links as $link)
                                <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            @if (($social['visible'] ?? false) || ($schedule['visible'] ?? false))
                <div class="col-lg-4 col-md-12">
                    <div class="footer__widget">
                        @if ($schedule['visible'] ?? false)
                            <h6>Jam Operasional</h6>
                            <p>{{ $schedule['text'] }}</p>
                        @endif
                        @if ($social['visible'] ?? false)
                            <div class="footer__widget__social mt-3">
                                <a href="{{ $social['text'] }}" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="footer__copyright text-center">
                    @if (!empty($copyright))
                        <div class="footer__copyright__text"><p>{{ $copyright }}</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
