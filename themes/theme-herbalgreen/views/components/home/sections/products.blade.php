<section id="products" class="products">
    <h2>{{ $settings['products.heading'] ?? 'Products' }}</h2>
    <div class="product-grid">
        @foreach ($products as $product)
            @php $img = optional($product->images()->first())->path; @endphp
            <div class="product-card">
                <img src="{{ $img ? asset('storage/'.$img) : 'https://via.placeholder.com/150' }}" alt="{{ $product->name }}">
                <h3>{{ $product->name }}</h3>
                <p>{{ $product->description }}</p>
            </div>
        @endforeach
    </div>
</section>
