@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\PageElements;
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;

    $themeName = $theme ?? 'theme-second';
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

    $activeSections = PageElements::activeSectionKeys($themeName, $settings);
    $showHeroSection = in_array('hero', $activeSections, true);
    $showFiltersSection = in_array('filters', $activeSections, true);
    $showGridSection = in_array('grid', $activeSections, true);

    $heroVisible = ($settings['hero.visible'] ?? '1') === '1';
    $heroMask = ($settings['hero.mask'] ?? '0') === '1';
    $heroImage = !empty($settings['hero.background'])
        ? asset('storage/' . ltrim($settings['hero.background'], '/'))
        : asset('storage/themes/theme-second/img/breadcrumb.jpg');
    $filterVisible = $showFiltersSection && ($settings['filters.visible'] ?? '1') === '1' && ($categoryCollection->isNotEmpty() || $hasUncategorized);
    $filterHeading = $settings['filters.heading'] ?? 'Kategori';
    $allLabel = $settings['filters.all_label'] ?? 'Semua';
    $gridHeading = $settings['grid.heading'] ?? 'Galeri Kami';
    $emptyText = $settings['grid.empty_text'] ?? 'Belum ada foto untuk ditampilkan.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['hero.heading'] ?? 'Galeri' }}</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
</head>
<body>
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if($showHeroSection && $heroVisible)
<section id="hero" class="breadcrumb-section set-bg {{ $heroMask ? 'breadcrumb-section--mask' : '' }}" data-setbg="{{ $heroImage }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.heading'] ?? 'Galeri' }}</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ url('/') }}">Home</a>
                        <span>{{ $settings['hero.heading'] ?? 'Galeri' }}</span>
                    </div>
                    @if(!empty($settings['hero.description']))
                        <p class="mt-3 text-white-50">{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section id="gallery" class="blog spad gallery-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4">
                @if($filterVisible)
                <div class="blog__sidebar" data-gallery-filter>
                    <div class="blog__sidebar__item">
                        <h4>{{ $filterHeading }}</h4>
                        <ul>
                            <li><a href="#" class="is-active" data-filter="all">{{ $allLabel }}</a></li>
                            @foreach($categoryCollection as $category)
                                <li><a href="#" data-filter="{{ $category->slug }}">{{ $category->name }}</a></li>
                            @endforeach
                            @if($hasUncategorized)
                                <li><a href="#" data-filter="__uncategorized__">Tanpa Kategori</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-lg-8 col-md-8">
                <div class="blog__item">
                    <div class="blog__item__text">
                        <h4 class="mb-4">{{ $gridHeading }}</h4>
                        @if($showGridSection)
                            @if($itemCollection->isEmpty())
                                <div class="alert alert-light border">{{ $emptyText }}</div>
                            @else
                            <div class="row g-4 gallery__grid" data-gallery-grid>
                                @foreach($itemCollection as $item)
                                    @php
                                        $categorySlug = $item->category?->slug ?? '__uncategorized__';
                                        $categoryName = $item->category?->name ?? 'Tanpa Kategori';
                                        $imageUrl = asset('storage/' . ltrim($item->image_path, '/'));
                                    @endphp
                                    <div class="col-lg-6 col-md-6 col-sm-6" data-category="{{ $categorySlug }}">
                                        <div class="gallery__card" data-gallery-open data-image="{{ $imageUrl }}" data-title="{{ e($item->title) }}" data-description="{{ e($item->description ?? '') }}" data-category-name="{{ e($categoryName) }}">
                                            <div class="gallery__card__image" style="background-image:url('{{ $imageUrl }}')"></div>
                                            <div class="gallery__card__content">
                                                <h6>{{ $item->title }}</h6>
                                                <span>{{ $categoryName }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        @endif
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

<script src="{{ asset('storage/themes/theme-second/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/jquery.slicknav.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/mixitup.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('storage/themes/theme-second/js/main.js') }}"></script>
@once
    <style>
        .gallery-page .blog__item {
            border: none;
            padding: 0;
        }
        .gallery__grid {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 1.5rem;
        }
        .gallery__card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gallery__card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }
        .gallery__card__image {
            padding-top: 70%;
            background-size: cover;
            background-position: center;
        }
        .gallery__card__content {
            padding: 1rem 1.25rem 1.5rem;
        }
        .gallery__card__content h6 {
            margin-bottom: 0.35rem;
            font-weight: 700;
        }
        .gallery__card__content span {
            color: #888;
            font-size: 0.85rem;
        }
        .blog__sidebar ul li a.is-active {
            color: #7fad39;
            font-weight: 700;
        }
        .gallery-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            z-index: 1050;
        }
        .gallery-modal[hidden] {
            display: none;
        }
        .gallery-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.65);
        }
        .gallery-modal__dialog {
            position: relative;
            max-width: 720px;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
        }
        .gallery-modal__close {
            position: absolute;
            top: 0.75rem;
            right: 1rem;
            font-size: 2rem;
            border: none;
            background: transparent;
            color: #fff;
            cursor: pointer;
        }
        .gallery-modal__dialog img {
            width: 100%;
            height: 420px;
            object-fit: cover;
            background: #000;
        }
        .gallery-modal__caption {
            padding: 1.75rem 2rem 2rem;
        }
        .gallery-modal__caption h3 {
            margin-bottom: 0.5rem;
        }
        .gallery-modal__meta {
            color: #7fad39;
            font-weight: 600;
            margin-bottom: 1rem;
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
            const filterContainer = document.querySelector('[data-gallery-filter]');
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

            filterContainer?.addEventListener('click', function(event){
                const link = event.target.closest('a[data-filter]');
                if(!link) return;
                event.preventDefault();
                const slug = link.getAttribute('data-filter');
                filterContainer.querySelectorAll('a[data-filter]').forEach(function(anchor){
                    anchor.classList.toggle('is-active', anchor === link);
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

{!! view()->file(base_path('themes/' . $themeName . '/views/components/floating-contact-buttons.blade.php'), [
    'theme' => $themeName,
])->render() !!}
</body>
</html>
