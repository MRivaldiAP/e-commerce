<div id="products" class="container-xxl py-5">
    <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
            <h1 class="mb-5">{{ $settings['products.heading'] ?? 'Most Popular Items' }}</h1>
        </div>
        <div class="row g-4">
            @foreach($products as $product)
            @php $img = $product->image_url ?? optional($product->images()->first())->path; @endphp
            <div class="col-lg-6">
                <div class="d-flex align-items-center">
                    <img class="flex-shrink-0 img-fluid rounded" src="{{ $img ? asset('storage/'.$img) : asset('storage/themes/theme-restoran/img/menu-1.jpg') }}" alt="{{ $product->name }}" style="width: 80px;">
                    <div class="w-100 d-flex flex-column text-start ps-4">
                        <h5 class="d-flex justify-content-between border-bottom pb-2">
                            <span>{{ $product->name }}</span>
                            <span class="text-primary">{{ $product->price_formatted ?? number_format($product->price, 0, ',', '.') }}</span>
                        </h5>
                        <small class="fst-italic">{{ $product->description }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
