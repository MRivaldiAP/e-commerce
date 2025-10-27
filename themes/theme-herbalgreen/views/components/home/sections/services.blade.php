<section id="services" class="services">
    <h2>{{ $settings['services.heading'] ?? 'Our Services' }}</h2>
    <ul>
        @foreach($services as $svc)
            <li>{{ $svc['title'] ?? '' }}</li>
        @endforeach
    </ul>
</section>
