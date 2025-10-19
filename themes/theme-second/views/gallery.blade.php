<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
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
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        if (Str::startsWith($trimmed, ['http://', 'https://', '//'])) {
            return $trimmed;
        }
        return asset('storage/' . ltrim($trimmed, '/'));
    };

    $categories = collect(json_decode($settings['categories.items'] ?? '[]', true));
    $categories = $categories->map(function ($item, $index) {
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
    $heroMask = ($settings['hero.mask'] ?? '0') === '1';
    $heroImage = $resolveMedia($settings['hero.image'] ?? null) ?? asset('storage/themes/theme-second/img/breadcrumb.jpg');
    $pageTitle = $settings['hero.heading'] ?? 'Galeri';
@endphp
    <title>{{ $pageTitle }}</title>
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('storage/themes/theme-second/css/jquery-ui.min.css') }}" type="text/css">
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

@if(($settings['hero.visible'] ?? '1') == '1')
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

@if(($settings['gallery.visible'] ?? '1') == '1')
<section id="grid" class="blog spad">
    <div class="container">
        <div class="row">
            @if($showFilters)
            <div class="col-lg-3 col-md-4">
                <div class="blog__sidebar">
                    <div id="filters" class="blog__sidebar__item">
                        <h4>{{ $filtersHeading }}</h4>
                        <ul>
                            <li><a href="#" class="active" data-gallery-filter="*">{{ $allLabel }}</a></li>
                            @foreach($categories as $category)
                                <li><a href="#" data-gallery-filter="{{ $category['slug'] }}">{{ $category['name'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            <div class="{{ $showFilters ? 'col-lg-9 col-md-8' : 'col-lg-12' }}">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="section-title from-blog__title">
                            <h2>{{ $galleryHeading }}</h2>
                        </div>
                    </div>
                    @forelse($galleryItems as $item)
                    <div class="col-lg-4 col-md-6 col-sm-6" data-gallery-item data-category="{{ $item['category'] }}">
                        <div class="blog__item">
                            <div class="blog__item__pic">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                            </div>
                            <div class="blog__item__text">
                                <h5>{{ $item['title'] }}</h5>
                                @if(!empty($item['category_label']))
                                    <span class="text-muted small d-block mb-2">{{ $item['category_label'] }}</span>
                                @endif
                                <a href="#" class="blog__btn" data-gallery-open data-image="{{ $item['image'] }}" data-title="{{ e($item['title']) }}" data-description="{{ e($item['description']) }}">Lihat <span class="arrow_right"></span></a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-light border">{{ $emptyText }}</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<div class="modal fade" id="galleryModal" tabindex="-1" role="dialog" aria-labelledby="galleryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="" alt="" id="galleryModalImage" class="img-fluid w-100 rounded mb-3">
                <p id="galleryModalCaption" class="mb-0"></p>
            </div>
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
(function($){
    const $filters = $('[data-gallery-filter]');
    const $items = $('[data-gallery-item]');

    $filters.on('click', function(e){
        e.preventDefault();
        const filter = $(this).data('gallery-filter');
        $filters.removeClass('active');
        $(this).addClass('active');
        $items.each(function(){
            const $item = $(this);
            const category = $item.data('category') || '';
            const visible = filter === '*' || category === filter;
            $item.toggleClass('d-none', !visible);
        });
    });

    $('[data-gallery-open]').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        $('#galleryModalTitle').text($btn.data('title') || '');
        $('#galleryModalImage').attr('src', $btn.data('image') || '');
        $('#galleryModalCaption').text($btn.data('description') || '');
        $('#galleryModal').modal('show');
    });
})(jQuery);
</script>
</body>
</html>
