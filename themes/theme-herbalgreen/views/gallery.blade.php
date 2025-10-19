<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri</title>
    <link rel="stylesheet" href="{{ asset('themes/' . ($theme ?? 'theme-herbalgreen') . '/theme.css') }}">
    <script src="{{ asset('themes/' . ($theme ?? 'theme-herbalgreen') . '/theme.js') }}" defer></script>
</head>
<body>
@php
    use App\Models\PageSetting;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Models\GalleryCategory;
    use App\Models\GalleryItem;

    $themeName = $theme ?? 'theme-herbalgreen';
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
    $heroMask = ($settings['hero.mask'] ?? '0') === '1';
    $heroBackground = !empty($settings['hero.background']) ? asset('storage/' . ltrim($settings['hero.background'], '/')) : null;
    $filterVisible = ($settings['filters.visible'] ?? '1') === '1' && ($categoryCollection->isNotEmpty() || $hasUncategorized);
    $filterHeading = $settings['filters.heading'] ?? 'Kategori Galeri';
    $allLabel = $settings['filters.all_label'] ?? 'Semua Foto';
    $gridHeading = $settings['grid.heading'] ?? 'Galeri Kami';
    $emptyText = $settings['grid.empty_text'] ?? 'Belum ada foto di galeri.';
@endphp
{!! view()->file(base_path('themes/' . $themeName . '/views/components/nav-menu.blade.php'), [
    'brand' => $navigation['brand'],
    'links' => $navigation['links'],
    'showCart' => $navigation['show_cart'],
    'showLogin' => $navigation['show_login'],
    'cart' => $cartSummary,
])->render() !!}

@if($heroVisible)
<section id="hero" class="hero gallery-hero {{ $heroMask ? 'gallery-hero--mask' : '' }}" @if($heroBackground) style="background-image:url('{{ $heroBackground }}')" @endif>
    <div class="hero-content">
        <span class="tagline">{{ $settings['hero.heading'] ?? 'Galeri' }}</span>
        @if(!empty($settings['hero.description']))
        <p>{{ $settings['hero.description'] }}</p>
        @endif
    </div>
</section>
@endif

<section id="gallery" class="gallery-section">
    <div class="gallery-wrapper">
        @if($filterVisible)
        <aside id="filters" class="gallery-filters" data-gallery-filter>
            <h3>{{ $filterHeading }}</h3>
            <ul>
                <li><button type="button" class="is-active" data-filter="all">{{ $allLabel }}</button></li>
                @foreach($categoryCollection as $category)
                    <li><button type="button" data-filter="{{ $category->slug }}">{{ $category->name }}</button></li>
                @endforeach
                @if($hasUncategorized)
                    <li><button type="button" data-filter="__uncategorized__">Tanpa Kategori</button></li>
                @endif
            </ul>
        </aside>
        @endif
        <div class="gallery-content">
            <div class="gallery-heading">
                <h2>{{ $gridHeading }}</h2>
            </div>
            @if($itemCollection->isEmpty())
                <div class="gallery-empty">{{ $emptyText }}</div>
            @else
            <div class="gallery-grid" data-gallery-grid>
                @foreach($itemCollection as $item)
                    @php
                        $categorySlug = $item->category?->slug ?? '__uncategorized__';
                        $categoryName = $item->category?->name ?? 'Tanpa Kategori';
                        $imageUrl = asset('storage/' . ltrim($item->image_path, '/'));
                    @endphp
                    <figure class="gallery-card" data-category="{{ $categorySlug }}">
                        <button type="button" class="gallery-card__link" data-gallery-open data-image="{{ $imageUrl }}" data-title="{{ e($item->title) }}" data-description="{{ e($item->description ?? '') }}" data-category-name="{{ e($categoryName) }}">
                            <img src="{{ $imageUrl }}" alt="{{ $item->title }}">
                            <figcaption>
                                <span class="gallery-card__title">{{ $item->title }}</span>
                                <span class="gallery-card__category">{{ $categoryName }}</span>
                            </figcaption>
                        </button>
                    </figure>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

<div class="gallery-modal" data-gallery-modal hidden>
    <div class="gallery-modal__backdrop" data-gallery-close></div>
    <div class="gallery-modal__dialog">
        <button type="button" class="gallery-modal__close" data-gallery-close aria-label="Tutup">×</button>
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

@once
    <style>
        .gallery-hero {
            min-height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center;
            position: relative;
            text-align: center;
            padding: 3rem 1.5rem;
        }
        .gallery-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.65);
        }
        .gallery-hero--mask::before {
            background: rgba(27, 94, 32, 0.45);
        }
        .gallery-hero .hero-content {
            position: relative;
            max-width: 560px;
        }
        .gallery-section {
            padding: 4rem 2rem;
        }
        .gallery-wrapper {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 2.5rem;
        }
        .gallery-filters {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            padding: 2rem;
        }
        .gallery-filters h3 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
        }
        .gallery-filters ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 0.5rem;
        }
        .gallery-filters button {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border-radius: 999px;
            border: 1px solid rgba(46, 125, 50, 0.2);
            background: transparent;
            font-weight: 600;
            color: var(--color-text);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .gallery-filters button:hover,
        .gallery-filters button.is-active {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
        }
        .gallery-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .gallery-heading h2 {
            margin: 0;
            text-align: left;
        }
        .gallery-empty {
            padding: 3rem;
            border-radius: 12px;
            background: #fff;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .gallery-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
        .gallery-card {
            margin: 0;
        }
        .gallery-card__link {
            display: block;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: none;
            padding: 0;
            text-align: left;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gallery-card__link:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        }
        .gallery-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }
        .gallery-card figcaption {
            padding: 1rem 1.25rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .gallery-card__title {
            font-weight: 700;
            color: var(--color-text);
        }
        .gallery-card__category {
            font-size: 0.85rem;
            color: rgba(27, 94, 32, 0.7);
        }
        .gallery-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            z-index: 999;
        }
        .gallery-modal[hidden] {
            display: none;
        }
        .gallery-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.6);
        }
        .gallery-modal__dialog {
            position: relative;
            background: #fff;
            border-radius: 16px;
            max-width: 720px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .gallery-modal__close {
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            border: none;
            background: transparent;
            font-size: 2rem;
            color: #fff;
            cursor: pointer;
        }
        .gallery-modal__dialog img {
            width: 100%;
            height: 420px;
            object-fit: cover;
            display: block;
            background: #000;
        }
        .gallery-modal__caption {
            padding: 1.5rem 2rem 2rem;
            color: var(--color-text);
        }
        .gallery-modal__caption h3 {
            margin-top: 0;
            margin-bottom: 0.75rem;
        }
        .gallery-modal__meta {
            margin: 0 0 1rem;
            font-weight: 600;
            color: rgba(27, 94, 32, 0.75);
        }
        @media (min-width: 992px) {
            .gallery-wrapper {
                grid-template-columns: 280px minmax(0, 1fr);
            }
            .gallery-filters {
                position: sticky;
                top: 6rem;
            }
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
        document.addEventListener('DOMContentLoaded', function () {
            const filterContainer = document.querySelector('[data-gallery-filter]');
            const grid = document.querySelector('[data-gallery-grid]');
            const modal = document.querySelector('[data-gallery-modal]');
            const modalImage = modal?.querySelector('[data-gallery-modal-image]');
            const modalTitle = modal?.querySelector('[data-gallery-modal-title]');
            const modalCategory = modal?.querySelector('[data-gallery-modal-category]');
            const modalDescription = modal?.querySelector('[data-gallery-modal-description]');
            let activeFilter = 'all';

            function applyFilter(slug) {
                activeFilter = slug;
                if (!grid) return;
                grid.querySelectorAll('[data-category]').forEach(function (item) {
                    const itemSlug = item.getAttribute('data-category');
                    const show = slug === 'all' || itemSlug === slug;
                    item.style.display = show ? '' : 'none';
                });
            }

            filterContainer?.addEventListener('click', function (event) {
                const button = event.target.closest('button[data-filter]');
                if (!button) return;
                const slug = button.getAttribute('data-filter');
                filterContainer.querySelectorAll('button[data-filter]').forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === button);
                });
                applyFilter(slug);
            });

            grid?.addEventListener('click', function (event) {
                const trigger = event.target.closest('[data-gallery-open]');
                if (!trigger || !modal) return;
                const image = trigger.getAttribute('data-image');
                const title = trigger.getAttribute('data-title');
                const description = trigger.getAttribute('data-description');
                const category = trigger.getAttribute('data-category-name');
                modalImage.src = image;
                modalImage.alt = title;
                modalTitle.textContent = title;
                modalCategory.textContent = category;
                modalDescription.textContent = description || '';
                modal.removeAttribute('hidden');
                modal.setAttribute('aria-hidden', 'false');
            });

            modal?.addEventListener('click', function (event) {
                if (event.target.hasAttribute('data-gallery-close')) {
                    modal.setAttribute('hidden', 'hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    modalImage.src = '';
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal && !modal.hasAttribute('hidden')) {
                    modal.setAttribute('hidden', 'hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    modalImage.src = '';
                }
            });

            applyFilter(activeFilter);
        });
    </script>
@endonce
</body>
</html>
