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

        $themeName = $theme ?? 'theme-herbalgreen';
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
        $heroImage = $resolveMedia($settings['hero.image'] ?? null);
        $pageTitle = $settings['hero.heading'] ?? 'Galeri';
    @endphp
    <title>{{ $pageTitle }} - Herbal Green</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $themeName . '/theme.css') }}">
    <script src="{{ asset('themes/' . $themeName . '/theme.js') }}" defer></script>
    <style>
        .gallery-section { padding: 4rem 2rem; }
        .gallery-layout { display: grid; gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .gallery-layout.has-filters { grid-template-columns: 260px 1fr; }
        .gallery-filters { background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(46,125,50,0.12); padding: 1.5rem; position: sticky; top: 120px; height: fit-content; }
        .gallery-filters h3 { margin-top: 0; margin-bottom: 1rem; font-size: 1.25rem; color: var(--color-primary); }
        .gallery-filters ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.75rem; }
        .gallery-filters button { width: 100%; padding: 0.65rem 0.75rem; border-radius: 999px; border: 1px solid rgba(46,125,50,0.2); background: transparent; color: var(--color-text); font-weight: 500; cursor: pointer; transition: background 0.2s, color 0.2s, border-color 0.2s; }
        .gallery-filters button.is-active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
        .gallery-content h2 { margin-top: 0; margin-bottom: 1.5rem; text-align: center; color: var(--color-primary); }
        .gallery-grid { display: grid; gap: 1.75rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .gallery-card { background: #fff; border-radius: 12px; box-shadow: 0 10px 24px rgba(76,175,80,0.12); padding: 1.5rem; text-align: center; display: flex; flex-direction: column; gap: 1rem; transition: transform 0.2s, box-shadow 0.2s; }
        .gallery-card:hover { transform: translateY(-6px); box-shadow: 0 14px 28px rgba(76,175,80,0.18); }
        .gallery-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
        .gallery-card .category-label { font-size: 0.85rem; color: var(--color-secondary); text-transform: uppercase; letter-spacing: 1px; }
        .gallery-card button { align-self: center; padding: 0.6rem 1.5rem; border-radius: 999px; border: none; background: var(--color-primary); color: #fff; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .gallery-card button:hover { background: var(--color-accent); }
        .gallery-empty { text-align: center; padding: 2rem; border-radius: 12px; background: rgba(76,175,80,0.08); color: var(--color-primary); }
        .gallery-modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(27,94,32,0.75); backdrop-filter: blur(2px); z-index: 2000; padding: 2rem; }
        .gallery-modal[hidden] { display: none; }
        .gallery-modal__content { background: #fff; border-radius: 16px; max-width: 720px; width: 100%; padding: 1.5rem; box-shadow: 0 18px 40px rgba(0,0,0,0.25); position: relative; }
        .gallery-modal__close { position: absolute; top: 0.75rem; right: 0.75rem; border: none; background: transparent; font-size: 1.75rem; line-height: 1; cursor: pointer; color: var(--color-primary); }
        .gallery-modal img { width: 100%; border-radius: 12px; margin-bottom: 1rem; max-height: 420px; object-fit: cover; }
        .gallery-modal__title { margin: 0 0 0.75rem; color: var(--color-primary); }
        .gallery-modal__description { margin: 0; color: rgba(0,0,0,0.7); }
        @media (max-width: 960px) { .gallery-layout.has-filters { grid-template-columns: 1fr; } .gallery-filters { position: relative; top: auto; } }
    </style>
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
<section id="hero" class="hero" @if($heroImage) style="background-image:url('{{ $heroImage }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.heading'] ?? 'Galeri' }}</span>
        <h1>{{ $settings['hero.heading'] ?? 'Galeri' }}</h1>
        @if(!empty($settings['hero.description']))
        <p>{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</section>
@endif

@if(($settings['gallery.visible'] ?? '1') == '1')
<section id="grid" class="gallery-section">
    <div class="gallery-layout {{ $showFilters ? 'has-filters' : '' }}">
        @if($showFilters)
        <aside id="filters" class="gallery-filters">
            <h3>{{ $filtersHeading }}</h3>
            <ul>
                <li><button type="button" class="is-active" data-gallery-filter="*">{{ $allLabel }}</button></li>
                @foreach($categories as $category)
                    <li><button type="button" data-gallery-filter="{{ $category['slug'] }}">{{ $category['name'] }}</button></li>
                @endforeach
            </ul>
        </aside>
        @endif
        <div class="gallery-content">
            <h2>{{ $galleryHeading }}</h2>
            <div class="gallery-grid">
                @forelse($galleryItems as $item)
                <div class="gallery-card" data-gallery-item data-category="{{ $item['category'] }}">
                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                    @if(!empty($item['category_label']))
                        <span class="category-label">{{ $item['category_label'] }}</span>
                    @endif
                    <h3>{{ $item['title'] }}</h3>
                    <button type="button" data-gallery-open data-image="{{ $item['image'] }}" data-title="{{ e($item['title']) }}" data-description="{{ e($item['description']) }}">Lihat</button>
                </div>
                @empty
                <div class="gallery-empty">{{ $emptyText }}</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endif

<div id="gallery-modal" class="gallery-modal" hidden>
    <div class="gallery-modal__content">
        <button type="button" class="gallery-modal__close" data-gallery-close>&times;</button>
        <h3 class="gallery-modal__title" id="gallery-modal-title"></h3>
        <img src="" alt="" id="gallery-modal-image">
        <p class="gallery-modal__description" id="gallery-modal-description"></p>
    </div>
</div>

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}

<script>
(function(){
    const filters = document.querySelectorAll('[data-gallery-filter]');
    const items = document.querySelectorAll('[data-gallery-item]');
    const modal = document.getElementById('gallery-modal');
    const modalTitle = document.getElementById('gallery-modal-title');
    const modalImage = document.getElementById('gallery-modal-image');
    const modalDescription = document.getElementById('gallery-modal-description');

    filters.forEach(function(button){
        button.addEventListener('click', function(){
            const filter = this.getAttribute('data-gallery-filter');
            filters.forEach(btn => btn.classList.toggle('is-active', btn === button));
            items.forEach(function(item){
                const category = item.getAttribute('data-category') || '';
                const visible = filter === '*' || category === filter;
                item.style.display = visible ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('[data-gallery-open]').forEach(function(trigger){
        trigger.addEventListener('click', function(){
            modalTitle.textContent = this.getAttribute('data-title') || '';
            modalImage.setAttribute('src', this.getAttribute('data-image') || '');
            modalDescription.textContent = this.getAttribute('data-description') || '';
            modal.removeAttribute('hidden');
        });
    });

    modal.addEventListener('click', function(event){
        if(event.target === modal || event.target.hasAttribute('data-gallery-close')){
            modal.setAttribute('hidden', 'hidden');
        }
    });
})();
</script>
</body>
</html>
