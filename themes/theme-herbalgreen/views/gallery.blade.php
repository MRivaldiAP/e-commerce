<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri</title>
    <link rel="stylesheet" href="{{ asset('themes/' . $theme . '/theme.css') }}">
    <script src="{{ asset('themes/' . $theme . '/theme.js') }}" defer></script>
    <style>
        .gallery-container { display: grid; grid-template-columns: 260px 1fr; gap: 2rem; }
        .gallery-sidebar { background: #f5f7f3; border-radius: 16px; padding: 1.5rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.04); }
        .gallery-sidebar h3 { margin-top: 0; font-size: 1.25rem; font-weight: 600; }
        .gallery-sidebar ul { list-style: none; padding: 0; margin: 1rem 0 0; display: flex; flex-direction: column; gap: .5rem; }
        .gallery-sidebar button { background: transparent; border: none; text-align: left; padding: .5rem .75rem; border-radius: 10px; font-weight: 500; cursor: pointer; transition: background .2s ease, color .2s ease; }
        .gallery-sidebar button.active, .gallery-sidebar button:hover { background: #274b3b; color: #fff; }
        .gallery-header { margin-bottom: 1.5rem; }
        .gallery-header h2 { font-size: 2rem; margin: 0 0 .5rem; color: #274b3b; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; }
        .gallery-card { background: #fff; border-radius: 16px; box-shadow: 0 18px 28px rgba(24,39,75,0.08); overflow: hidden; cursor: pointer; transition: transform .2s ease, box-shadow .2s ease; display: flex; flex-direction: column; }
        .gallery-card:hover { transform: translateY(-4px); box-shadow: 0 22px 32px rgba(24,39,75,0.12); }
        .gallery-card img { width: 100%; height: 200px; object-fit: cover; }
        .gallery-card .gallery-card-body { padding: 1.25rem; display: flex; flex-direction: column; gap: .5rem; }
        .gallery-card .gallery-tag { display: inline-flex; align-items: center; gap: .35rem; font-size: .85rem; font-weight: 600; color: #274b3b; text-transform: uppercase; letter-spacing: .06em; }
        .gallery-card h4 { margin: 0; font-size: 1.1rem; color: #1f2d3d; }
        .gallery-card p { margin: 0; color: #5f6b6d; font-size: .95rem; }
        .gallery-empty { background: #f5f7f3; border-radius: 16px; padding: 2rem; text-align: center; color: #5f6b6d; }
        .gallery-modal { position: fixed; inset: 0; background: rgba(18, 32, 47, 0.75); display: none; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1050; }
        .gallery-modal.open { display: flex; }
        .gallery-modal__panel { background: #fff; border-radius: 18px; overflow: hidden; max-width: 840px; width: 100%; box-shadow: 0 24px 48px rgba(18,32,47,0.22); display: grid; grid-template-columns: 1fr; }
        .gallery-modal__image { background: #111; display: flex; align-items: center; justify-content: center; }
        .gallery-modal__image img { max-height: 70vh; width: 100%; object-fit: contain; }
        .gallery-modal__body { padding: 1.75rem 2rem; display: flex; flex-direction: column; gap: .75rem; }
        .gallery-modal__close { position: absolute; top: 1.5rem; right: 2rem; font-size: 2rem; color: #fff; cursor: pointer; }
        @media (max-width: 992px) {
            .gallery-container { grid-template-columns: 1fr; }
            .gallery-sidebar { display: flex; flex-wrap: wrap; gap: .75rem; }
            .gallery-sidebar h3 { width: 100%; }
            .gallery-sidebar ul { flex-direction: row; flex-wrap: wrap; }
        }
        @media (max-width: 576px) {
            .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
            .gallery-card img { height: 180px; }
        }
    </style>
</head>
<body>
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
        if (! $value) { return null; }
        if (Str::startsWith($value, ['http://', 'https://', '//'])) { return $value; }
        if (Str::startsWith($value, ['/storage', 'storage/'])) { return asset(ltrim($value, '/')); }
        return asset('storage/' . ltrim($value, '/'));
    };

    $rawCategories = collect(json_decode($settings['gallery.categories'] ?? '[]', true));
    $categories = $rawCategories->filter(fn ($item) => is_array($item))
        ->map(function ($item) {
            $name = trim($item['name'] ?? '');
            $slug = trim($item['slug'] ?? '');
            if ($slug === '') {
                $slug = Str::slug($name ?: uniqid('kategori'));
            }
            if ($name === '') {
                $name = Str::title(str_replace('-', ' ', $slug));
            }
            return ['name' => $name, 'slug' => $slug];
        })
        ->unique('slug')
        ->values();

    $categoryMap = $categories->keyBy('slug');
    $allLabel = $settings['filters.all_label'] ?? 'Semua';

    $rawItems = collect(json_decode($settings['gallery.items'] ?? '[]', true));
    $items = $rawItems->filter(fn ($item) => is_array($item) && ! empty($item['image']))
        ->map(function ($item) use ($resolveMedia, $categoryMap) {
            $title = trim($item['title'] ?? '');
            $category = trim($item['category'] ?? '');
            if ($category === '' && $categoryMap->isNotEmpty()) {
                $category = $categoryMap->keys()->first();
            }
            $image = $resolveMedia($item['image'] ?? null) ?? 'https://via.placeholder.com/400x260?text=Gallery';
            $description = trim($item['description'] ?? '');
            return [
                'title' => $title !== '' ? $title : 'Galeri',
                'category' => $category,
                'image' => $image,
                'description' => $description,
            ];
        })
        ->values();

    $heroBackground = $resolveMedia($settings['hero.image'] ?? null);
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}
@if(($settings['hero.visible'] ?? '1') == '1')
<section id="hero" class="hero" @if($heroBackground) style="background-image:url('{{ $heroBackground }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.heading'] ?? 'Galeri' }}</span>
        @if(!empty($settings['hero.description']))
            <p>{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</section>
@endif
<section id="gallery" class="section">
    <div class="container">
        <div class="gallery-container">
            @if(($settings['filters.visible'] ?? '1') == '1')
            <aside class="gallery-sidebar">
                <h3>{{ $settings['filters.heading'] ?? 'Kategori' }}</h3>
                <ul>
                    <li><button type="button" class="active" data-filter="">{{ $allLabel }}</button></li>
                    @foreach($categories as $category)
                        <li><button type="button" data-filter="{{ $category['slug'] }}">{{ $category['name'] }}</button></li>
                    @endforeach
                </ul>
            </aside>
            @endif
            <div class="gallery-content">
                @if(($settings['items.visible'] ?? '1') == '1')
                <div class="gallery-header" id="items">
                    <h2>{{ $settings['items.heading'] ?? 'Galeri Kami' }}</h2>
                    @if(!empty($settings['items.description']))
                        <p>{{ $settings['items.description'] }}</p>
                    @endif
                </div>
                @if($items->isEmpty())
                    <div class="gallery-empty">Belum ada item galeri yang ditambahkan.</div>
                @else
                <div class="gallery-grid" data-gallery-grid>
                    @foreach($items as $item)
                    <article class="gallery-card" data-category="{{ $item['category'] }}" data-image="{{ $item['image'] }}" data-title="{{ $item['title'] }}" data-description="{{ $item['description'] }}" data-category-label="{{ $categoryMap[$item['category']]['name'] ?? $item['category'] }}">
                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                        <div class="gallery-card-body">
                            @if(!empty($item['category']))
                                <span class="gallery-tag">{{ $categoryMap[$item['category']]['name'] ?? $item['category'] }}</span>
                            @endif
                            <h4>{{ $item['title'] }}</h4>
                            @if(!empty($item['description']))
                                <p>{{ $item['description'] }}</p>
                            @endif
                        </div>
                    </article>
                    @endforeach
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</section>
<div class="gallery-modal" id="gallery-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="gallery-modal__close" data-modal-close>&times;</div>
    <div class="gallery-modal__panel">
        <div class="gallery-modal__image">
            <img src="" alt="Galeri" data-modal-image>
        </div>
        <div class="gallery-modal__body">
            <span class="gallery-tag" data-modal-category></span>
            <h3 data-modal-title></h3>
            <p data-modal-description></p>
        </div>
    </div>
</div>
{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
])->render() !!}
<script>
    (function(){
        const filters = document.querySelectorAll('.gallery-sidebar [data-filter]');
        const cards = document.querySelectorAll('[data-gallery-grid] [data-category], .gallery-grid [data-category]');
        const modal = document.getElementById('gallery-modal');
        const modalImage = modal.querySelector('[data-modal-image]');
        const modalTitle = modal.querySelector('[data-modal-title]');
        const modalDescription = modal.querySelector('[data-modal-description]');
        const modalCategory = modal.querySelector('[data-modal-category]');

        filters.forEach(function(button){
            button.addEventListener('click', function(){
                const target = this.getAttribute('data-filter');
                filters.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                cards.forEach(function(card){
                    const category = card.getAttribute('data-category');
                    card.style.display = (!target || category === target) ? '' : 'none';
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
