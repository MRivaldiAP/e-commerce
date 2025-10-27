@php
    $visible = $intro['visible'] ?? false;
    $image = $intro['image'] ?? null;
    $heading = $intro['heading'] ?? 'Cerita Kami';
    $description = $intro['description'] ?? 'Kami menyajikan pengalaman kuliner terbaik dengan bahan pilihan dan layanan penuh kehangatan.';
    $badge = $intro['badge'] ?? 'Tentang Kami';
@endphp
@if($visible)
<div id="intro" class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="position-relative">
                    <img class="img-fluid rounded w-100" src="{{ $image ?: asset('storage/themes/theme-restoran/img/about-1.jpg') }}" alt="{{ $heading }}">
                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-primary px-3 py-2">{{ $badge }}</span>
                </div>
            </div>
            <div class="col-lg-6">
                <h5 class="section-title ff-secondary text-start text-primary fw-normal">{{ $heading }}</h5>
                <p class="mb-4">{{ $description }}</p>
            </div>
        </div>
    </div>
</div>
@endif
