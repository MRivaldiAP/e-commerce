<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/style.css') }}" type="text/css">
    <style>
        .gallery__filters ul li a.active { color: #7fad39; font-weight: 600; }
        .gallery__item { cursor: pointer; transition: transform .2s ease; }
        .gallery__item:hover { transform: translateY(-4px); }
        .gallery__thumb { position: relative; overflow: hidden; border-radius: 8px; }
        .gallery__thumb img { width: 100%; height: 260px; object-fit: cover; display: block; }
        .gallery__thumb::after { content: '\f002'; font-family: 'FontAwesome'; position: absolute; inset: 0; background: rgba(0,0,0,0.45); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; opacity: 0; transition: opacity .2s ease; }
        .gallery__item:hover .gallery__thumb::after { opacity: 1; }
        .gallery-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.75); display: none; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1050; }
        .gallery-modal.open { display: flex; }
        .gallery-modal__content { max-width: 960px; width: 100%; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1.5rem 3rem rgba(0,0,0,0.25); }
        .gallery-modal__image { width: 100%; height: 0; padding-top: 56.25%; position: relative; }
        .gallery-modal__image img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #111; }
        .gallery-modal__body { padding: 1.5rem; }
        .gallery-modal__close { position: absolute; top: 1rem; right: 1rem; color: #fff; font-size: 2rem; cursor: pointer; }
        @media (max-width: 767.98px) {
            .gallery__thumb img { height: 200px; }
        }
    </style>
</head>
<body>
@php
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-second';
    $settings = PageSetting::forPage('gallery');
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);
    $cartSummary = Cart::summary();

    $resolveMedia = function (?string $value) {
        if (! $value) {
            return null;
        }
        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
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
                'image' => $resolveMedia($item->image_path) ?? asset('storage/themes/theme-second/img/blog/blog-1.jpg'),
                'description' => $item->description ?? '',
            ];
        });

    $heroImage = $resolveMedia($settings['hero.image'] ?? null) ?? asset('storage/themes/theme-second/img/breadcrumb.jpg');
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}
@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="breadcrumb-section set-bg" data-setbg="{{ $heroImage }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $settings['hero.heading'] ?? 'Galeri' }}</h2>
                    @if(!empty($settings['hero.description']))
                        <p>{{ $settings['hero.description'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif
<section id="filters" class="product spad">
    <div class="container">
        <div class="row">
            @if(($settings['filters.visible'] ?? '1') == '1')
            <div class="col-lg-3 col-md-5 mb-4 mb-md-0">
                <div class="sidebar gallery__filters">
                    <div class="sidebar__item">
                        <h4>{{ $settings['filters.heading'] ?? 'Kategori' }}</h4>
                        <ul>
                            <li><a href="#" data-filter="" class="active">{{ $allLabel }}</a></li>
                            @foreach($categories as $category)
                                <li><a href="#" data-filter="{{ $category['slug'] }}">{{ $category['name'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-lg-9 col-md-7">
                @if(($settings['items.visible'] ?? '1') == '1')
                <div id="items" class="gallery__header mb-4">
                    <div class="section-title">
                        <h2>{{ $settings['items.heading'] ?? 'Galeri Kami' }}</h2>
                        @if(!empty($settings['items.description']))
                            <p>{{ $settings['items.description'] }}</p>
                        @endif
                    </div>
                </div>
                <div class="row" data-gallery-grid>
                    @forelse($items as $item)
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-4" data-category="{{ $item['category'] }}">
                            <div class="gallery__item" data-image="{{ $item['image'] }}" data-title="{{ $item['title'] }}" data-description="{{ $item['description'] }}" data-category-label="{{ $categoryMap[$item['category']]['name'] ?? $item['category'] }}">
                                <div class="gallery__thumb mb-3">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                </div>
                                <div class="blog__item__text">
                                    @if(!empty($item['category']))
                                    <ul>
                                        <li><i class="fa fa-tag"></i> {{ $categoryMap[$item['category']]['name'] ?? $item['category'] }}</li>
                                    </ul>
                                    @endif
                                    <h5 class="mb-2">{{ $item['title'] }}</h5>
                                    @if(!empty($item['description']))
                                        <p class="mb-0">{{ $item['description'] }}</p>
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
</section>
<div class="gallery-modal" id="gallery-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="gallery-modal__close" data-modal-close>&times;</div>
    <div class="gallery-modal__content">
        <div class="gallery-modal__image">
            <img src="" alt="Galeri" data-modal-image>
        </div>
        <div class="gallery-modal__body">
            <p class="text-muted mb-1" data-modal-category></p>
            <h4 class="mb-3" data-modal-title></h4>
            <p class="mb-0" data-modal-description></p>
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
<script>
    (function(){
        const filterLinks = document.querySelectorAll('.gallery__filters [data-filter]');
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
                    if (!target || category === target) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        document.querySelectorAll('.gallery__item').forEach(function(item){
            item.addEventListener('click', function(){
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
