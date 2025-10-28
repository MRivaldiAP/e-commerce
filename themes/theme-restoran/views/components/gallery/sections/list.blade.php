<div id="gallery" class="container py-5">
    <div class="row g-4">
        @if($filterVisible)
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 6rem;">
                    <div class="card-body">
                        <h5 class="card-title mb-3">{{ $filterHeading }}</h5>
                        <ul class="list-group list-group-flush" data-gallery-filter>
                            <li class="list-group-item px-0">
                                <button type="button" class="btn btn-link p-0 text-start w-100 gallery-filter is-active" data-filter="all">
                                    {{ $allLabel }}
                                </button>
                            </li>
                            @foreach($categoryCollection as $category)
                                <li class="list-group-item px-0">
                                    <button type="button" class="btn btn-link p-0 text-start w-100 gallery-filter" data-filter="{{ $category->slug }}">
                                        {{ $category->name }}
                                    </button>
                                </li>
                            @endforeach
                            @if($hasUncategorized)
                                <li class="list-group-item px-0">
                                    <button type="button" class="btn btn-link p-0 text-start w-100 gallery-filter" data-filter="__uncategorized__">
                                        Tanpa Kategori
                                    </button>
                                </li>
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
                            <div class="card border-0 shadow-sm h-100 gallery-card" data-gallery-open
                                data-image="{{ $imageUrl }}"
                                data-title="{{ e($item->title) }}"
                                data-description="{{ e($item->description ?? '') }}"
                                data-category-name="{{ e($categoryName) }}">
                                <div class="ratio ratio-4x3 bg-light rounded-top"
                                    style="background-image:url('{{ $imageUrl }}'); background-size:cover; background-position:center;"></div>
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
            background: rgba(var(--theme-accent-rgb), 0.75);
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
