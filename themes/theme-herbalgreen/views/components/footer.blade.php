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
<footer id="footer">
    @if ($showHotlinks && count($links))
        <ul class="footer-links">
            @foreach ($links as $link)
                <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
            @endforeach
        </ul>
    @endif
    <div class="footer-info">
        @if ($address['visible'] ?? false)
            <p class="footer-info__item">📍 {{ $address['text'] }}</p>
        @endif
        @if ($phone['visible'] ?? false)
            <p class="footer-info__item">📞 <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone['text']) }}">{{ $phone['text'] }}</a></p>
        @endif
        @if ($email['visible'] ?? false)
            <p class="footer-info__item">✉️ <a href="mailto:{{ $email['text'] }}">{{ $email['text'] }}</a></p>
        @endif
        @if ($social['visible'] ?? false)
            <p class="footer-info__item">🔗 <a href="{{ $social['text'] }}" target="_blank" rel="noopener">{{ $social['text'] }}</a></p>
        @endif
        @if ($schedule['visible'] ?? false)
            <p class="footer-info__item">🕒 {{ $schedule['text'] }}</p>
        @endif
    </div>
    @if (!empty($copyright))
        <p class="footer-copy">{{ $copyright }}</p>
    @endif
</footer>

@once
    <style>
        #footer {
            margin-top: 4rem;
            padding: 2.5rem 2rem;
            background: var(--color-dark, #1b1b1b);
            color: #fff;
            text-align: center;
        }

        #footer .footer-links {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        #footer .footer-links a,
        #footer .footer-info a {
            color: inherit;
            text-decoration: none;
        }

        #footer .footer-info {
            display: grid;
            gap: 0.35rem;
            margin-bottom: 1.5rem;
        }

        #footer .footer-info__item {
            margin: 0;
            font-size: 0.95rem;
        }

        #footer .footer-copy {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.75;
        }
    </style>
@endonce
