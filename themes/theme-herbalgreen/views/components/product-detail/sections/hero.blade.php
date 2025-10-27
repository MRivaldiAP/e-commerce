<section id="hero" class="hero" @if($heroImage) style="background-image:url('{{ $heroImage }}')" @endif>
    <div class="hero-content">
        <h1>{{ $settings['hero.title'] ?? $product->name }}</h1>
        <p>{{ $product->short_description ?? 'Temukan detail lengkap produk pilihan Anda.' }}</p>
    </div>
</section>
