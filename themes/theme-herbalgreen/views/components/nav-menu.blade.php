<header id="navigation" class="site-header">
    <div class="logo">TEA</div>
    <nav class="main-nav">
        <ul>
            @foreach ($links as $link)
                @if ($link['visible'])
                    <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                @endif
            @endforeach
        </ul>
    </nav>
    <div class="header-icons">
        <span>🔍</span>
        <span>👤</span>
        <span>🛒</span>
    </div>
</header>
