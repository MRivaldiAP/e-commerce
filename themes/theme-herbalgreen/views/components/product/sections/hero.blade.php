<section id="hero" class="hero" @if($heroImage) style="background-image:url('{{ $heroImage }}')" @endif>
    <div class="hero-content">
        <h1>{{ $settings['title'] ?? 'Produk Kami' }}</h1>
    </div>
</section>
