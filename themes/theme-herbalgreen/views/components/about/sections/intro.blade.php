<section id="intro" class="about-intro">
    <div class="about-intro__grid">
        <div class="about-intro__image">
            @php $image = $resolveMedia($settings['intro.image'] ?? null); @endphp
            @if($image)
                <img src="{{ $image }}" alt="{{ $settings['intro.heading'] ?? 'Tentang Kami' }}">
            @endif
        </div>
        <div class="about-intro__content">
            <h2>{{ $settings['intro.heading'] ?? 'Perjalanan Kami' }}</h2>
            <p>{{ $settings['intro.description'] ?? 'Kami percaya pada kekuatan alam untuk menghadirkan kehidupan yang lebih sehat dan seimbang.' }}</p>
        </div>
    </div>
</section>
