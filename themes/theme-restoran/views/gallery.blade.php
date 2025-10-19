<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    @php
        use App\Models\PageSetting;
        use App\Support\Cart;
        use App\Support\LayoutSettings;
        use App\Support\ThemeMedia;
        use Illuminate\Support\Str;

        $themeName = $theme ?? 'theme-restoran';
        $settings = PageSetting::forPage('gallery');
        $navigation = LayoutSettings::navigation($themeName);
        $footerConfig = LayoutSettings::footer($themeName);
        $cartSummary = Cart::summary();

        $resolveMedia = function (?string $value) {
            if (! $value) {
                return null;
            }
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }
            if (Str::startsWith($trimmed, ['http://', 'https://', '//'])) {
                return $trimmed;
            }
            return ThemeMedia::url($trimmed) ?? asset('storage/' . ltrim($trimmed, '/'));
        };

        $categories = collect(json_decode($settings['categories.items'] ?? '[]', true))->map(function ($item, $index) {
            $name = trim((string)($item['name'] ?? ''));
            $slug = trim((string)($item['slug'] ?? ''));
            if ($slug === '') {
                $slug = $name !== '' ? Str::slug($name) : 'kategori-' . ($index + 1);
            } else {
                $slug = Str::slug($slug);
            }
            if ($name === '') {
                $name = 'Kategori ' . ($index + 1);
            }
            return ['name' => $name, 'slug' => $slug];
        })->filter(fn ($item) => $item['slug'] !== '')->unique('slug')->values();

        $categoriesMap = $categories->keyBy('slug');

        $galleryItemsRaw = json_decode($settings['gallery.items'] ?? '[]', true);
        if (! is_array($galleryItemsRaw)) {
            $galleryItemsRaw = [];
        }

        $galleryItems = collect($galleryItemsRaw)->map(function ($item, $index) use ($categoriesMap, $resolveMedia) {
            $image = $resolveMedia($item['image'] ?? null);
            if (! $image) {
                return null;
            }
            $title = trim((string)($item['title'] ?? ''));
            $description = trim((string)($item['description'] ?? ''));
            $categoryRaw = trim((string)($item['category'] ?? ''));
            $categorySlug = $categoryRaw !== '' ? Str::slug($categoryRaw) : null;
            if ($categorySlug === null && $categoriesMap->isNotEmpty()) {
                $categorySlug = $categoriesMap->keys()->first();
            }
            $categoryLabel = $categorySlug && $categoriesMap->has($categorySlug)
                ? $categoriesMap[$categorySlug]['name']
                : ($categoryRaw !== '' ? $categoryRaw : '');

            return [
                'title' => $title !== '' ? $title : 'Galeri ' . ($index + 1),
                'description' => $description,
                'image' => $image,
                'category' => $categorySlug ?? '',
                'category_label' => $categoryLabel,
            ];
        })->filter()->values();

        $showFilters = ($settings['categories.visible'] ?? '1') == '1' && $categories->isNotEmpty();
        $allLabel = $settings['categories.all_label'] ?? 'Semua';
        $filtersHeading = $settings['categories.heading'] ?? 'Kategori';
        $galleryHeading = $settings['gallery.heading'] ?? 'Galeri';
        $emptyText = $settings['gallery.empty_text'] ?? 'Belum ada foto galeri.';

        $heroMaskEnabled = ($settings['hero.mask'] ?? '1') === '1';
        $heroBackground = $resolveMedia($settings['hero.image'] ?? null) ?? asset('storage/themes/theme-restoran/img/breadcrumb.jpg');
        $heroClasses = 'container-xxl py-5 hero-header mb-5' . ($heroMaskEnabled ? ' bg-dark' : '');
        if (! $heroMaskEnabled) {
            $heroClasses .= ' hero-no-mask';
        }
        $pageTitle = $settings['hero.heading'] ?? 'Galeri';
    @endphp
    <title>{{ $pageTitle }}</title>
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
        #grid .card img { height: 200px; object-fit: cover; }
        #grid .list-group-button { border: 1px solid rgba(254,161,22,0.25); border-radius: 999px; margin-bottom: 0.75rem; padding: 0.6rem 1rem; background: transparent; color: var(--dark); font-weight: 600; width: 100%; transition: all 0.2s ease; }
        #grid .list-group-button.active { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary); }
        #grid .gallery-badge { background: rgba(254,161,22,0.15); color: #FEA116; }
        #grid .sticky-widget { position: sticky; top: 120px; }
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
    @if(($settings['hero.visible'] ?? '1') == '1')
    <div id="hero" class="{{ $heroClasses }}" style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center;">
        <div class="container text-center my-5 pt-5 pb-4">
            <h1 class="display-3 text-white mb-3">{{ $settings['hero.heading'] ?? 'Galeri' }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center text-uppercase">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">{{ $settings['hero.heading'] ?? 'Galeri' }}</li>
                </ol>
            </nav>
            @if(!empty($settings['hero.description']))
                <p class="text-white-50 mb-0">{{ $settings['hero.description'] }}</p>
            @endif
        </div>
    </div>
    @endif
</div>

@if(($settings['gallery.visible'] ?? '1') == '1')
<div id="grid" class="container py-5">
    <div class="row g-4">
        @if($showFilters)
        <div class="col-lg-3">
            <div id="filters" class="card border-0 shadow-sm sticky-widget">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ $filtersHeading }}</h4>
                    <button type="button" class="list-group-button active" data-gallery-filter="*">{{ $allLabel }}</button>
                    @foreach($categories as $category)
                        <button type="button" class="list-group-button" data-gallery-filter="{{ $category['slug'] }}">{{ $category['name'] }}</button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <div class="{{ $showFilters ? 'col-lg-9' : 'col-lg-12' }}">
            <div class="row g-4">
                <div class="col-12 text-center">
                    <h2 class="fw-bold text-primary">{{ $galleryHeading }}</h2>
                </div>
                @forelse($galleryItems as $item)
                <div class="col-md-6 col-lg-4" data-gallery-item data-category="{{ $item['category'] }}">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ $item['image'] }}" class="card-img-top" alt="{{ $item['title'] }}">
                        <div class="card-body d-flex flex-column">
                            @if(!empty($item['category_label']))
                                <span class="badge gallery-badge mb-2 align-self-start">{{ $item['category_label'] }}</span>
                            @endif
                            <h5 class="card-title">{{ $item['title'] }}</h5>
                            @if(!empty($item['description']))
                                <p class="card-text text-muted flex-grow-1">{{ $item['description'] }}</p>
                            @else
                                <div class="flex-grow-1"></div>
                            @endif
                            <button type="button" class="btn btn-primary mt-3 align-self-start" data-gallery-open data-image="{{ $item['image'] }}" data-title="{{ e($item['title']) }}" data-description="{{ e($item['description']) }}">Lihat Detail</button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-light border text-center">{{ $emptyText }}</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" alt="" id="galleryModalImage" class="img-fluid rounded mb-3">
                <p id="galleryModalDescription" class="mb-0"></p>
            </div>
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
<script>
(function(){
    const filterButtons = document.querySelectorAll('[data-gallery-filter]');
    const items = document.querySelectorAll('[data-gallery-item]');
    const modalElement = document.getElementById('galleryModal');
    const modalTitle = document.getElementById('galleryModalTitle');
    const modalImage = document.getElementById('galleryModalImage');
    const modalDescription = document.getElementById('galleryModalDescription');
    const galleryModal = modalElement ? new bootstrap.Modal(modalElement) : null;

    filterButtons.forEach(function(button){
        button.addEventListener('click', function(){
            const filter = button.getAttribute('data-gallery-filter');
            filterButtons.forEach(btn => btn.classList.toggle('active', btn === button));
            items.forEach(function(item){
                const category = item.getAttribute('data-category') || '';
                const visible = filter === '*' || category === filter;
                item.classList.toggle('d-none', !visible);
            });
        });
    });

    document.querySelectorAll('[data-gallery-open]').forEach(function(trigger){
        trigger.addEventListener('click', function(){
            if (! galleryModal) {
                return;
            }
            modalTitle.textContent = this.getAttribute('data-title') || '';
            modalImage.setAttribute('src', this.getAttribute('data-image') || '');
            modalDescription.textContent = this.getAttribute('data-description') || '';
            galleryModal.show();
        });
    });
})();
</script>
</body>
</html>
