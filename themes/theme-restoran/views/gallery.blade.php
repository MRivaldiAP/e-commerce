<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Galeri - Restoran Theme</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
    <style>
        :root { --bs-primary: #FEA116; --bs-primary-rgb: 254,161,22; }
        .hero-header {background-size: cover; background-position: center; position: relative;}
        .hero-header::after {content:''; position:absolute; inset:0; background:rgba(15,23,43,0.55);}
        .hero-header > .container { position: relative; z-index: 1; }
        .gallery-filter .list-group-item { border: none; padding-left: 0; font-weight: 500; }
        .gallery-filter .list-group-item.active { color: var(--bs-primary); background: transparent; }
        .gallery-card { border-radius: 12px; overflow: hidden; box-shadow: 0 1.25rem 2rem rgba(15, 23, 43, 0.08); background: #fff; cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; }
        .gallery-card:hover { transform: translateY(-6px); box-shadow: 0 1.75rem 2.5rem rgba(15, 23, 43, 0.14); }
        .gallery-card img { width: 100%; height: 220px; object-fit: cover; }
        .gallery-card .card-body { padding: 1.5rem; }
        .gallery-modal { position: fixed; inset: 0; background: rgba(15, 23, 43, 0.8); display: none; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1080; }
        .gallery-modal.open { display: flex; }
        .gallery-modal__panel { background: #fff; border-radius: 16px; overflow: hidden; max-width: 960px; width: 100%; box-shadow: 0 2rem 4rem rgba(0,0,0,0.2); }
        .gallery-modal__image { background: #0f172b; display: flex; align-items: center; justify-content: center; }
        .gallery-modal__image img { max-height: 70vh; width: 100%; object-fit: contain; }
        .gallery-modal__body { padding: 1.5rem 2rem 2rem; }
        .gallery-modal__close { position: absolute; top: 1.25rem; right: 1.75rem; font-size: 2.25rem; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
@php
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;

    $themeName = $theme ?? 'theme-restoran';
    $settings = PageSetting::forPage('gallery');
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $resolveMedia = function (?string $value, ?string $fallback = null) {
        return ThemeMedia::url($value) ?? $fallback;
    };

    $categories = GalleryCategory::orderBy('name')
        ->get()
        ->map(fn ($category) => [
            'name' => $category->name,
            'slug' => $category->slug,
        ]);

    $categoryMap = $categories->keyBy('slug');
    $allLabel = $settings['filters.all_label'] ?? 'Semua';

    $items = GalleryItem::with('category')
        ->orderBy('position')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($item) use ($resolveMedia, $categoryMap) {
            $categorySlug = $item->category?->slug;
            if (! $categorySlug && $categoryMap->isNotEmpty()) {
                $categorySlug = $categoryMap->keys()->first();
            }

            return [
                'title' => $item->title,
                'category' => $categorySlug ?? '',
                'image' => $resolveMedia($item->image_path, asset('storage/themes/theme-restoran/img/menu-1.jpg')),
                'description' => $item->description ?? '',
            ];
        });

    $heroBackground = $resolveMedia($settings['hero.image'] ?? null, asset('storage/themes/theme-restoran/img/bg-hero.jpg'));
    $heroStyle = $heroBackground ? "background-image: url('{$heroBackground}');" : '';
@endphp
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    @if(($settings['hero.visible'] ?? '1') == '1')
    <div id="hero" class="container-xxl py-5 hero-header mb-5" style="{{ $heroStyle }}">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-4 text-white mb-3">{{ $settings['hero.heading'] ?? 'Galeri' }}</h1>
            @if(!empty($settings['hero.description']))
                <p class="text-white-50 mb-0">{{ $settings['hero.description'] }}</p>
            @endif
        </div>
    </div>
    @endif
</div>
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5">
            @if(($settings['filters.visible'] ?? '1') == '1')
            <div class="col-lg-3 col-md-4">
                <div id="filters" class="gallery-filter">
                    <h5 class="mb-3">{{ $settings['filters.heading'] ?? 'Kategori' }}</h5>
                    <div class="list-group">
                        <a href="#" class="list-group-item active" data-filter="">{{ $allLabel }}</a>
                        @foreach($categories as $category)
                            <a href="#" class="list-group-item" data-filter="{{ $category['slug'] }}">{{ $category['name'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            <div class="col-lg-9 col-md-8">
                @if(($settings['items.visible'] ?? '1') == '1')
                <div id="items" class="mb-4">
                    <h2 class="section-title ff-secondary text-start text-primary fw-normal">{{ $settings['items.heading'] ?? 'Galeri Kami' }}</h2>
                    @if(!empty($settings['items.description']))
                        <p class="mb-0 text-muted">{{ $settings['items.description'] }}</p>
                    @endif
                </div>
                <div class="row g-4" data-gallery-grid>
                    @forelse($items as $index => $item)
                    <div class="col-lg-4 col-md-6" data-category="{{ $item['category'] }}">
                        <div class="gallery-card h-100" data-image="{{ $item['image'] }}" data-title="{{ $item['title'] }}" data-description="{{ $item['description'] }}" data-category-label="{{ $categoryMap[$item['category']]['name'] ?? $item['category'] }}">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                            <div class="card-body">
                                @if(!empty($item['category']))
                                <span class="badge bg-light text-dark mb-2"><i class="fa fa-tag me-1 text-primary"></i>{{ $categoryMap[$item['category']]['name'] ?? $item['category'] }}</span>
                                @endif
                                <h5 class="card-title mb-2">{{ $item['title'] }}</h5>
                                @if(!empty($item['description']))
                                <p class="card-text text-muted mb-0">{{ $item['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info">Belum ada item galeri yang ditambahkan.</div>
                    </div>
                    @endforelse
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="gallery-modal" id="gallery-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="gallery-modal__close" data-modal-close>&times;</div>
    <div class="gallery-modal__panel">
        <div class="gallery-modal__image">
            <img src="" alt="Galeri" data-modal-image>
        </div>
        <div class="gallery-modal__body">
            <p class="text-primary fw-semibold mb-1" data-modal-category></p>
            <h4 class="mb-3" data-modal-title></h4>
            <p class="mb-0 text-muted" data-modal-description></p>
        </div>
    </div>
</div>
{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
<script>
    (function(){
        const filterLinks = document.querySelectorAll('.gallery-filter [data-filter]');
        const items = document.querySelectorAll('[data-gallery-grid] [data-category]');
        const modal = document.getElementById('gallery-modal');
        const modalImage = modal.querySelector('[data-modal-image]');
        const modalTitle = modal.querySelector('[data-modal-title]');
        const modalDescription = modal.querySelector('[data-modal-description]');
        const modalCategory = modal.querySelector('[data-modal-category]');

        filterLinks.forEach(function(link){
            link.addEventListener('click', function(e){
                e.preventDefault();
                const target = this.getAttribute('data-filter');
                filterLinks.forEach(function(other){ other.classList.remove('active'); });
                this.classList.add('active');
                items.forEach(function(item){
                    const category = item.getAttribute('data-category');
                    item.style.display = (!target || category === target) ? '' : 'none';
                });
            });
        });

        document.querySelectorAll('.gallery-card').forEach(function(card){
            card.addEventListener('click', function(){
                modalImage.src = this.getAttribute('data-image');
                modalTitle.textContent = this.getAttribute('data-title');
                modalDescription.textContent = this.getAttribute('data-description') || '';
                modalCategory.textContent = this.getAttribute('data-category-label') || '';
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        });

        modal.addEventListener('click', function(e){
            if (e.target === modal || e.target.hasAttribute('data-modal-close')) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('keyup', function(e){
            if (e.key === 'Escape' && modal.classList.contains('open')) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    })();
</script>
</body>
</html>
