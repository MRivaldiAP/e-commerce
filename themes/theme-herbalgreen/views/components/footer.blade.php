<footer id="footer">
    <ul class="footer-links">
        @foreach ($links as $link)
            @if ($link['visible'])
                <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
            @endif
        @endforeach
    </ul>
    <p>{{ $copyright }}</p>
