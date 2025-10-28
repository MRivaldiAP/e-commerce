@php
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;

    $themeName = $theme ?? 'theme-istudio';
    $settings = $settings ?? PageSetting::forPage('gallery');
    $cartSummary = $cartSummary ?? Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $categoryCollection = collect($categories ?? GalleryCategory::orderBy('name')->get());
    $itemCollection = collect($items ?? GalleryItem::with('category')
        ->orderByRaw('position IS NULL')
        ->orderBy('position')
        ->orderBy('created_at', 'desc')
        ->get());

    $hasUncategorized = $itemCollection->contains(function ($item) {
        if (is_array($item)) {
            return empty($item['category']);
        }

        return $item->category === null;
    });

    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroBackground = $resolveMedia($settings['hero.background'] ?? $settings['hero.image'] ?? null, $assetBase('img/hero-slider-1.jpg'));

    $filterVisible = ($settings['filters.visible'] ?? '1') === '1' && ($categoryCollection->isNotEmpty() || $hasUncategorized);
    $filterHeading = $settings['filters.heading'] ?? 'Kategori Galeri';
    $allLabel = $settings['filters.all_label'] ?? 'Semua Foto';
    $gridHeading = $settings['grid.heading'] ?? 'Galeri Kami';
    $emptyText = $settings['grid.empty_text'] ?? 'Belum ada foto untuk ditampilkan.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['hero.heading'] ?? 'Galeri' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ $assetBase('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/style.css') }}" rel="stylesheet">
</head>
<body>
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if($heroVisible)
    <div id="hero" class="container-fluid pb-5 bg-primary hero-header" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container py-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <h1 class="display-1 mb-0 animated slideInLeft">{{ $settings['hero.heading'] ?? 'Galeri' }}</h1>
                </div>
                <div class="col-lg-6 animated slideInRight">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a class="text-primary" href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item text-secondary active" aria-current="page">{{ $settings['hero.heading'] ?? 'Galeri' }}</li>
                        </ol>
                    </nav>
                    @if(!empty($settings['hero.description']))
                        <p class="text-muted mt-3 mb-0">{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<section id="gallery" class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-0">
            <div class="col-lg-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column justify-content-center bg-primary h-100 p-5 rounded-start">
                    <h1 class="text-white mb-4">{{ $filterHeading }}</h1>
                    @if($filterVisible)
                        <div class="d-flex flex-column gap-2" data-gallery-filter>
                            <button type="button" class="btn btn-outline-light text-start px-3 gallery-filter is-active" data-filter="all">{{ $allLabel }}</button>
                            @foreach($categoryCollection as $category)
                                <button type="button" class="btn btn-outline-light text-start px-3 gallery-filter" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
                            @endforeach
                            @if($hasUncategorized)
                                <button type="button" class="btn btn-outline-light text-start px-3 gallery-filter" data-filter="__uncategorized__">Tanpa Kategori</button>
                            @endif
                        </div>
                    @else
                        <p class="text-white-50 mb-0">{{ $settings['hero.description'] ?? 'Jelajahi dokumentasi proyek terbaik kami.' }}</p>
                    @endif
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row g-0">
                    <div class="col-12">
                        <div class="bg-white h-100 p-4 p-lg-5">
                            <h2 class="mb-4">{{ $gridHeading }}</h2>
                            @if($itemCollection->isEmpty())
                                <div class="alert alert-light border">{{ $emptyText }}</div>
                            @else
                            <div class="row g-4" data-gallery-grid>
                                @foreach($itemCollection as $item)
                                    @php
                                        $isArray = is_array($item);
                                        $categorySlug = $isArray ? ($item['category']['slug'] ?? '__uncategorized__') : ($item->category?->slug ?? '__uncategorized__');
                                        $categoryName = $isArray ? ($item['category']['name'] ?? 'Tanpa Kategori') : ($item->category?->name ?? 'Tanpa Kategori');
                                        $title = $isArray ? ($item['title'] ?? 'Galeri') : ($item->title ?? 'Galeri');
                                        $description = $isArray ? ($item['description'] ?? '') : ($item->description ?? '');
                                        $imagePath = $isArray ? ($item['image_path'] ?? null) : ($item->image_path ?? null);
                                        $imageUrl = $resolveMedia($imagePath, $assetBase('img/project-1.jpg'));
                                    @endphp
                                    <div class="col-md-6" data-category="{{ $categorySlug }}">
                                        <div class="project-item position-relative overflow-hidden rounded" data-gallery-open data-image="{{ $imageUrl }}" data-title="{{ e($title) }}" data-description="{{ e($description) }}" data-category-name="{{ e($categoryName) }}">
                                            <img class="img-fluid w-100" src="{{ $imageUrl }}" alt="{{ $title }}">
                                            <a class="project-overlay text-decoration-none" href="javascript:void(0);" role="button">
                                                <div class="overlay-content">
                                                    <h4 class="text-white">{{ $title }}</h4>
                                                    <small class="text-white-50">{{ $categoryName }}</small>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="gallery-modal" data-gallery-modal hidden>
    <div class="gallery-modal__backdrop" data-gallery-close></div>
    <div class="gallery-modal__dialog">
        <button type="button" class="gallery-modal__close" data-gallery-close aria-label="Tutup">&times;</button>
        <img src="" alt="" data-gallery-modal-image>
        <div class="gallery-modal__caption">
            <h3 data-gallery-modal-title></h3>
            <p class="gallery-modal__meta" data-gallery-modal-category></p>
            <p data-gallery-modal-description></p>
        </div>
    </div>
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ $assetBase('lib/wow/wow.min.js') }}"></script>
<script src="{{ $assetBase('lib/easing/easing.min.js') }}"></script>
<script src="{{ $assetBase('lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ $assetBase('lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ $assetBase('js/main.js') }}"></script>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}

@once
<style>
    #gallery .gallery-filter {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    #gallery .gallery-filter.is-active,
    #gallery .gallery-filter:hover {
        background-color: #ffffff;
        color: #0F172B;
    }
    .project-item { cursor: pointer; }
    .project-item .project-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(rgba(15, 23, 43, 0.65), rgba(15, 23, 43, 0.85));
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        padding: 1.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .project-item:hover .project-overlay { opacity: 1; }
    .project-item .overlay-content h4 { margin-bottom: 0.25rem; }
    .gallery-modal {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        z-index: 2000;
    }
    .gallery-modal[hidden] { display: none !important; }
    .gallery-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.65);
    }
    .gallery-modal__dialog {
        position: relative;
        z-index: 1;
        max-width: 960px;
        width: 100%;
        background: #fff;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 25px 55px rgba(0, 0, 0, 0.25);
    }
    .gallery-modal__dialog img { width: 100%; height: auto; display: block; }
    .gallery-modal__caption { padding: 1.5rem; }
    .gallery-modal__caption h3 { margin-bottom: 0.5rem; }
    .gallery-modal__meta { color: #0F172B; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
    .gallery-modal__close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 2.5rem;
        height: 2.5rem;
        border: none;
        border-radius: 50%;
        background: rgba(15, 23, 43, 0.85);
        color: #fff;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
    }
    @media (max-width: 991.98px) {
        .gallery-modal {
            padding: 1rem;
        }
    }
</style>
<script>
    (function () {
        const filterContainer = document.querySelector('[data-gallery-filter]');
        const grid = document.querySelector('[data-gallery-grid]');
        const modal = document.querySelector('[data-gallery-modal]');
        const modalImage = modal?.querySelector('[data-gallery-modal-image]');
        const modalTitle = modal?.querySelector('[data-gallery-modal-title]');
        const modalCategory = modal?.querySelector('[data-gallery-modal-category]');
        const modalDescription = modal?.querySelector('[data-gallery-modal-description]');

        if (filterContainer && grid) {
            filterContainer.addEventListener('click', function (event) {
                const button = event.target.closest('[data-filter]');
                if (!button) {
                    return;
                }

                const filter = button.getAttribute('data-filter');
                filterContainer.querySelectorAll('.gallery-filter').forEach(function (el) {
                    el.classList.toggle('is-active', el === button);
                });

                grid.querySelectorAll('[data-category]').forEach(function (item) {
                    const category = item.getAttribute('data-category');
                    const shouldShow = filter === 'all' || filter === category;
                    item.style.display = shouldShow ? '' : 'none';
                });
            });
        }

        if (modal && grid) {
            grid.addEventListener('click', function (event) {
                const trigger = event.target.closest('[data-gallery-open]');
                if (!trigger) {
                    return;
                }
                const image = trigger.getAttribute('data-image');
                const title = trigger.getAttribute('data-title');
                const category = trigger.getAttribute('data-category-name');
                const description = trigger.getAttribute('data-description');

                if (modalImage) modalImage.src = image || '';
                if (modalTitle) modalTitle.textContent = title || '';
                if (modalCategory) modalCategory.textContent = category || '';
                if (modalDescription) modalDescription.textContent = description || '';

                modal.removeAttribute('hidden');
            });

            modal.addEventListener('click', function (event) {
                if (event.target.hasAttribute('data-gallery-close')) {
                    modal.setAttribute('hidden', 'hidden');
                }
            });
        }
    })();
</script>
@endonce
</body>
</html>
