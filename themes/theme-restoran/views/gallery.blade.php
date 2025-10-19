@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;

    $themeName = $theme ?? 'theme-restoran';
    $settings = PageSetting::forPage('gallery');
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $categoryCollection = collect($categories ?? GalleryCategory::orderBy('name')->get());
    $itemCollection = collect($items ?? GalleryItem::with('category')
        ->orderByRaw('position IS NULL')
        ->orderBy('position')
        ->orderBy('created_at', 'desc')
        ->get());

    $hasUncategorized = $itemCollection->contains(fn ($item) => $item->category === null);

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
    $heroBackground = ThemeMedia::url($settings['hero.image'] ?? $settings['hero.background'] ?? null)
        ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
    $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
    if (! $heroMaskEnabled) {
        $heroClasses .= ' hero-no-mask';
    }
    $heroStyle = '';
    if ($heroBackground) {
        if ($heroMaskEnabled) {
            $heroStyle = "background-image: linear-gradient(rgba(15, 23, 43, .85), rgba(15, 23, 43, .85)), url('{$heroBackground}'); background-size: cover; background-position: center;";
        } else {
            $heroStyle = "background-image: url('{$heroBackground}'); background-size: cover; background-position: center;";
        }
    } elseif (! $heroMaskEnabled) {
        $heroStyle = 'background-image: none;';
    }

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
    <title>{{ $settings['hero.heading'] ?? 'Galeri' }}</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('storage/themes/theme-restoran/img/favicon.ico') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('storage/themes/theme-restoran/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('storage/themes/theme-restoran/css/style.css') }}" rel="stylesheet">
    <style>
        :root{--bs-primary:#FEA116;--bs-primary-rgb:254,161,22;}
    </style>
</head>
<body>
<div class="container-xxl position-relative p-0">
    {!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
        'brand' => $navigation['brand'],
        'links' => $navigation['links'],
        'showCart' => $navigation['show_cart'],
        'showLogin' => $navigation['show_login'],
        'cart' => $cartSummary,
    ])->render() !!}
    @if($heroVisible)
    <div id="hero" class="{{ $heroClasses }}" style="{{ $heroStyle }}">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $settings['hero.heading'] ?? 'Galeri' }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">{{ $settings['hero.heading'] ?? 'Galeri' }}</li>
                </ol>
            </nav>
            @if(!empty($settings['hero.description']))
                <p class="text-white-50 mt-3">{{ $settings['hero.description'] }}</p>
            @endif
        </div>
    </div>
    @endif
</div>

<div id="gallery" class="container py-5">
    <div class="row g-4">
        @if($filterVisible)
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 6rem;">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ $filterHeading }}</h5>
                    <ul class="list-group list-group-flush" data-gallery-filter>
                        <li class="list-group-item px-0"><button type="button" class="btn btn-link p-0 text-start w-100 gallery-filter is-active" data-filter="all">{{ $allLabel }}</button></li>
                        @foreach($categoryCollection as $category)
                            <li class="list-group-item px-0"><button type="button" class="btn btn-link p-0 text-start w-100 gallery-filter" data-filter="{{ $category->slug }}">{{ $category->name }}</button></li>
                        @endforeach
                        @if($hasUncategorized)
                            <li class="list-group-item px-0"><button type="button" class="btn btn-link p-0 text-start w-100 gallery-filter" data-filter="__uncategorized__">Tanpa Kategori</button></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        @endif
        <div class="col-lg-8">
            <div class="mb-4">
                <h2 class="fw-bold">{{ $gridHeading }}</h2>
            </div>
            @if($itemCollection->isEmpty())
                <div class="alert alert-light border">{{ $emptyText }}</div>
            @else
            <div class="row g-4" data-gallery-grid>
                @foreach($itemCollection as $item)
                    @php
                        $categorySlug = $item->category?->slug ?? '__uncategorized__';
                        $categoryName = $item->category?->name ?? 'Tanpa Kategori';
                        $imageUrl = asset('storage/' . ltrim($item->image_path, '/'));
                    @endphp
                    <div class="col-md-6" data-category="{{ $categorySlug }}">
                        <div class="card border-0 shadow-sm h-100 gallery-card" data-gallery-open data-image="{{ $imageUrl }}" data-title="{{ e($item->title) }}" data-description="{{ e($item->description ?? '') }}" data-category-name="{{ e($categoryName) }}">
                            <div class="ratio ratio-4x3 bg-light rounded-top" style="background-image:url('{{ $imageUrl }}'); background-size:cover; background-position:center;"></div>
                            <div class="card-body">
                                <h5 class="card-title mb-1">{{ $item->title }}</h5>
                                <p class="card-text text-muted mb-0">{{ $categoryName }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<div class="gallery-modal" data-gallery-modal hidden>
    <div class="gallery-modal__backdrop" data-gallery-close></div>
    <div class="gallery-modal__dialog">
        <button type="button" class="gallery-modal__close" data-gallery-close aria-label="Tutup">&times;</button>
        <img src="" alt="" data-gallery-modal-image>
        <div class="gallery-modal__caption p-4">
            <h3 class="mb-2" data-gallery-modal-title></h3>
            <p class="text-primary fw-semibold mb-3" data-gallery-modal-category></p>
            <p class="mb-0" data-gallery-modal-description></p>
        </div>
    </div>
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/wow/wow.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/counterup/counterup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-restoran/js/main.js') }}"></script>
@once
    <style>
        .gallery-card {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gallery-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 45px rgba(0,0,0,0.15) !important;
        }
        .gallery-filter {
            color: inherit;
            font-weight: 600;
        }
        .gallery-filter:hover,
        .gallery-filter.is-active {
            color: var(--bs-primary);
        }
        .gallery-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            z-index: 2000;
        }
        .gallery-modal[hidden] {
            display: none;
        }
        .gallery-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 43, 0.75);
        }
        .gallery-modal__dialog {
            position: relative;
            max-width: 760px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
        }
        .gallery-modal__close {
            position: absolute;
            top: 0.5rem;
            right: 1rem;
            font-size: 2rem;
            border: none;
            background: transparent;
            color: #fff;
            z-index: 5;
            cursor: pointer;
        }
        .gallery-modal__dialog img {
            width: 100%;
            height: 440px;
            object-fit: cover;
            background: #000;
        }
        @media (max-width: 768px) {
            .gallery-modal {
                padding: 1rem;
            }
            .gallery-modal__dialog img {
                height: 260px;
            }
        }
    </style>
    <script>
        (function(){
            const filterList = document.querySelector('[data-gallery-filter]');
            const grid = document.querySelector('[data-gallery-grid]');
            const modal = document.querySelector('[data-gallery-modal]');
            const modalImage = modal?.querySelector('[data-gallery-modal-image]');
            const modalTitle = modal?.querySelector('[data-gallery-modal-title]');
            const modalCategory = modal?.querySelector('[data-gallery-modal-category]');
            const modalDescription = modal?.querySelector('[data-gallery-modal-description]');
            let activeFilter = 'all';

            function applyFilter(slug){
                activeFilter = slug;
                if(!grid) return;
                grid.querySelectorAll('[data-category]').forEach(function(item){
                    const category = item.getAttribute('data-category');
                    item.style.display = (slug === 'all' || category === slug) ? '' : 'none';
                });
            }

            filterList?.addEventListener('click', function(event){
                const button = event.target.closest('button[data-filter]');
                if(!button) return;
                event.preventDefault();
                const slug = button.getAttribute('data-filter');
                filterList.querySelectorAll('button[data-filter]').forEach(function(btn){
                    btn.classList.toggle('is-active', btn === button);
                });
                applyFilter(slug);
            });

            grid?.addEventListener('click', function(event){
                const card = event.target.closest('[data-gallery-open]');
                if(!card || !modal) return;
                const image = card.getAttribute('data-image');
                const title = card.getAttribute('data-title');
                const description = card.getAttribute('data-description');
                const category = card.getAttribute('data-category-name');
                modalImage.src = image;
                modalImage.alt = title;
                modalTitle.textContent = title;
                modalCategory.textContent = category;
                modalDescription.textContent = description || '';
                modal.removeAttribute('hidden');
                modal.setAttribute('aria-hidden', 'false');
            });

            modal?.addEventListener('click', function(event){
                if(event.target.hasAttribute('data-gallery-close')){
                    modal.setAttribute('hidden', 'hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    if(modalImage){ modalImage.src = ''; }
                }
            });

            document.addEventListener('keydown', function(event){
                if(event.key === 'Escape' && modal && !modal.hasAttribute('hidden')){
                    modal.setAttribute('hidden', 'hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    if(modalImage){ modalImage.src = ''; }
                }
            });

            applyFilter(activeFilter);
        })();
    </script>
@endonce
</body>
</html>
