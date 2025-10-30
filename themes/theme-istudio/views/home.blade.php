<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'iSTUDIO') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@php
    use App\Models\PageSetting;
    use App\Models\Product;
    use App\Support\Cart;
    use App\Support\LayoutSettings;
    use App\Support\ThemeMedia;
    use Illuminate\Support\Str;

    $themeName = $theme ?? 'theme-istudio';
    $assetBase = fn ($path) => asset('storage/themes/' . $themeName . '/' . ltrim($path, '/'));
    $settings = PageSetting::forPage('home');
    $products = Product::where('is_featured', true)->latest()->take(5)->with('promotions')->get();
    $cartSummary = Cart::summary();
    $navigation = LayoutSettings::navigation($themeName);
    $footerConfig = LayoutSettings::footer($themeName);

    $resolveMedia = function ($path, $fallback = null) {
        if (empty($path)) {
            return $fallback;
        }

        return ThemeMedia::url($path) ?? $fallback;
    };

    $normalizeChecklist = function ($items) {
        if (! is_array($items)) {
            return [];
        }

        $items = array_values(array_filter($items));

        $mapped = array_map(function ($item) {
            if (is_array($item)) {
                foreach (['text', 'label', 'value'] as $key) {
                    if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') {
                        return $item[$key];
                    }
                }

                $first = reset($item);

                return is_string($first) ? $first : '';
            }

            return is_string($item) ? $item : '';
        }, $items);

        return array_values(array_filter($mapped, fn ($value) => is_string($value) && trim($value) !== ''));
    };

    $heroSlides = json_decode($settings['hero.slides'] ?? '[]', true);
    $heroSlides = is_array($heroSlides) ? array_values(array_filter($heroSlides, fn ($item) => is_array($item))) : [];
    if ($heroSlides === []) {
        $heroSlides = [
            ['image' => $assetBase('img/hero-slider-1.jpg')],
            ['image' => $assetBase('img/hero-slider-2.jpg')],
            ['image' => $assetBase('img/hero-slider-3.jpg')],
        ];
    } else {
        $heroSlides = array_map(function ($slide) use ($resolveMedia, $assetBase) {
            $fallback = $assetBase('img/hero-slider-1.jpg');
            $image = $resolveMedia($slide['image'] ?? null, $fallback);

            return ['image' => $image];
        }, $heroSlides);
    }

    $heroHighlights = json_decode($settings['hero.highlights'] ?? '[]', true);
    $heroHighlights = is_array($heroHighlights) ? array_values(array_filter($heroHighlights, fn ($item) => is_array($item))) : [];
    if ($heroHighlights === []) {
        $heroHighlights = [
            ['icon' => 'fa fa-couch', 'label' => 'Crafted Furniture'],
            ['icon' => 'fa fa-leaf', 'label' => 'Sustainable Material'],
            ['icon' => 'fa fa-drafting-compass', 'label' => 'Innovative Architects'],
            ['icon' => 'fa fa-wallet', 'label' => 'Budget Friendly'],
        ];
    }

    $featureItems = json_decode($settings['features.items'] ?? '[]', true);
    $featureItems = is_array($featureItems) ? array_values(array_filter($featureItems, fn ($item) => is_array($item))) : [];
    if ($featureItems === []) {
        $featureItems = [
            ['icon' => 'fa fa-calendar-alt', 'title' => '25+ Years Experience', 'text' => 'Clita erat ipsum et lorem et sit, sed stet no labore lorem sit.'],
            ['icon' => 'fa fa-tasks', 'title' => 'Best Interior Design', 'text' => 'Aliqu diam amet diam et eos labore. Clita erat ipsum et lorem et sit.'],
            ['icon' => 'fa fa-pencil-ruler', 'title' => 'Innovative Architects', 'text' => 'Sanctus clita duo justo et tempor eirmod magna dolore erat amet.'],
            ['icon' => 'fa fa-users', 'title' => 'Customer Satisfaction', 'text' => 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum et tempor sit.'],
            ['icon' => 'fa fa-hand-holding-usd', 'title' => 'Budget Friendly', 'text' => 'Clita erat ipsum et lorem et sit, sed stet no labore lorem sit.'],
            ['icon' => 'fa fa-check', 'title' => 'Sustainable Material', 'text' => 'Sanctus clita duo justo et tempor eirmod magna dolore erat amet.'],
        ];
    }

    $projectItems = json_decode($settings['projects.items'] ?? '[]', true);
    $projectItems = is_array($projectItems) ? array_values(array_filter($projectItems, fn ($item) => is_array($item))) : [];
    if ($projectItems === []) {
        $projectItems = [
            ['image' => $assetBase('img/project-1.jpg'), 'title' => 'Kitchen', 'count' => '72 Projects'],
            ['image' => $assetBase('img/project-2.jpg'), 'title' => 'Bathroom', 'count' => '67 Projects'],
            ['image' => $assetBase('img/project-3.jpg'), 'title' => 'Bedroom', 'count' => '53 Projects'],
            ['image' => $assetBase('img/project-4.jpg'), 'title' => 'Living Room', 'count' => '33 Projects'],
            ['image' => $assetBase('img/project-5.jpg'), 'title' => 'Furniture', 'count' => '87 Projects'],
            ['image' => $assetBase('img/project-6.jpg'), 'title' => 'Renovation', 'count' => '69 Projects'],
        ];
    } else {
        $projectItems = array_map(function ($item, $index) use ($resolveMedia, $assetBase) {
            $fallbacks = [
                $assetBase('img/project-1.jpg'),
                $assetBase('img/project-2.jpg'),
                $assetBase('img/project-3.jpg'),
                $assetBase('img/project-4.jpg'),
                $assetBase('img/project-5.jpg'),
                $assetBase('img/project-6.jpg'),
            ];
            $fallback = $fallbacks[$index % count($fallbacks)];

            return [
                'image' => $resolveMedia($item['image'] ?? null, $fallback),
                'title' => $item['title'] ?? 'Project',
                'count' => $item['count'] ?? '',
                'link' => $item['link'] ?? '#!',
            ];
        }, $projectItems, array_keys($projectItems));
    }

    $services = json_decode($settings['services.items'] ?? '[]', true);
    $services = is_array($services) ? array_values(array_filter($services, fn ($item) => is_array($item))) : [];
    if ($services === []) {
        $services = [
            ['title' => 'Interior Design', 'text' => 'Erat ipsum justo amet duo et elitr dolor, est duo duo eos lorem sed diam.', 'image' => $assetBase('img/service-1.jpg')],
            ['title' => 'Implementation', 'text' => 'Sanctus clita duo justo et tempor eirmod magna dolore erat amet.', 'image' => $assetBase('img/service-2.jpg')],
            ['title' => 'Renovation', 'text' => 'Clita erat ipsum et lorem et sit, sed stet no labore lorem sit.', 'image' => $assetBase('img/service-3.jpg')],
            ['title' => 'Commercial', 'text' => 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum et tempor sit.', 'image' => $assetBase('img/service-4.jpg')],
        ];
    } else {
        $services = array_map(function ($item, $index) use ($resolveMedia, $assetBase) {
            $fallbacks = [
                $assetBase('img/service-1.jpg'),
                $assetBase('img/service-2.jpg'),
                $assetBase('img/service-3.jpg'),
                $assetBase('img/service-4.jpg'),
            ];
            $fallback = $fallbacks[$index % count($fallbacks)];

            return [
                'title' => $item['title'] ?? 'Service',
                'text' => $item['text'] ?? '',
                'image' => $resolveMedia($item['image'] ?? null, $fallback),
                'link' => $item['link'] ?? '#!',
            ];
        }, $services, array_keys($services));
    }

    $teamMembers = json_decode($settings['team.members'] ?? '[]', true);
    $teamMembers = is_array($teamMembers) ? array_values(array_filter($teamMembers, fn ($item) => is_array($item))) : [];
    if ($teamMembers === []) {
        $teamMembers = [
            ['name' => 'Boris Johnson', 'title' => 'Architect', 'photo' => $assetBase('img/team-1.jpg')],
            ['name' => 'Donald Pakura', 'title' => 'Architect', 'photo' => $assetBase('img/team-2.jpg')],
            ['name' => 'Bradley Gordon', 'title' => 'Architect', 'photo' => $assetBase('img/team-3.jpg')],
            ['name' => 'Alexander Bell', 'title' => 'Architect', 'photo' => $assetBase('img/team-4.jpg')],
        ];
    } else {
        $teamMembers = array_map(function ($member, $index) use ($resolveMedia, $assetBase) {
            $fallbacks = [
                $assetBase('img/team-1.jpg'),
                $assetBase('img/team-2.jpg'),
                $assetBase('img/team-3.jpg'),
                $assetBase('img/team-4.jpg'),
            ];
            $fallback = $fallbacks[$index % count($fallbacks)];
            $social = $member['social'] ?? [];
            if (is_string($social)) {
                $social = array_filter(array_map('trim', explode(',', $social)));
            }
            if (! is_array($social)) {
                $social = [];
            }

            return [
                'name' => $member['name'] ?? 'Team Member',
                'title' => $member['title'] ?? '',
                'photo' => $resolveMedia($member['photo'] ?? null, $fallback),
                'social' => $social,
            ];
        }, $teamMembers, array_keys($teamMembers));
    }

    $testimonials = json_decode($settings['testimonials.items'] ?? '[]', true);
    $testimonials = is_array($testimonials) ? array_values(array_filter($testimonials, fn ($item) => is_array($item))) : [];
    if ($testimonials === []) {
        $testimonials = [
            ['title' => 'Sustainable Material', 'text' => 'Aliqu diam amet diam et eos labore. Clita erat ipsum et lorem et sit.', 'name' => 'Boris Johnson', 'photo' => $assetBase('img/testimonial-1.jpg')],
            ['title' => 'Customer Satisfaction', 'text' => 'Clita erat ipsum et lorem et sit, sed stet no labore lorem sit.', 'name' => 'Alexander Bell', 'photo' => $assetBase('img/testimonial-2.jpg')],
            ['title' => 'Budget Friendly', 'text' => 'Diam amet diam et eos labore. Sanctus clita duo justo et tempor.', 'name' => 'Bradley Gordon', 'photo' => $assetBase('img/testimonial-3.jpg')],
        ];
    } else {
        $testimonials = array_map(function ($item, $index) use ($resolveMedia, $assetBase) {
            $fallbacks = [
                $assetBase('img/testimonial-1.jpg'),
                $assetBase('img/testimonial-2.jpg'),
                $assetBase('img/testimonial-3.jpg'),
            ];
            $fallback = $fallbacks[$index % count($fallbacks)];

            return [
                'title' => $item['title'] ?? '',
                'text' => $item['text'] ?? '',
                'name' => $item['name'] ?? '',
                'photo' => $resolveMedia($item['photo'] ?? null, $fallback),
            ];
        }, $testimonials, array_keys($testimonials));
    }

    $formatPrice = function ($value) {
        return number_format((float) $value, 0, ',', '.');
    };
@endphp
    <link href="{{ $assetBase('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ $assetBase('css/style.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }
        .price-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .price-original {
            font-size: 0.85rem;
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.65);
        }
        .price-current {
            font-size: 1.1rem;
            font-weight: 700;
        }
        .promo-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: #dc3545;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
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

@if(($settings['hero.visible'] ?? '1') === '1')
    <div class="container-fluid pb-5 hero-header bg-light mb-5">
        <div class="container py-5">
            <div class="row g-5 align-items-center mb-5">
                <div class="col-lg-6">
                    <h1 class="display-1 mb-4 animated slideInRight">{{ $settings['hero.heading'] ?? 'We Make Your <span class="text-primary">Home</span> Better' }}</h1>
                    <h5 class="d-inline-block border border-2 border-white py-3 px-5 mb-0 animated slideInRight">
                        {{ $settings['hero.tagline'] ?? 'An Award Winning Studio Since 1990' }}
                    </h5>
                    @if (!empty($settings['hero.button_label']))
                        <div class="mt-4">
                            <a href="{{ $settings['hero.button_link'] ?? '#!' }}" class="btn btn-primary py-3 px-4">{{ $settings['hero.button_label'] }}</a>
                        </div>
                    @endif
                </div>
                <div class="col-lg-6">
                    <div class="owl-carousel header-carousel animated fadeIn">
                        @foreach($heroSlides as $slide)
                            <img class="img-fluid" src="{{ $slide['image'] }}" alt="Hero slide {{ $loop->iteration }}">
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="row g-4 animated fadeIn">
                @foreach ($heroHighlights as $highlight)
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 btn-square border border-2 border-white me-3">
                                <i class="{{ $highlight['icon'] ?? 'fa fa-check' }} text-primary"></i>
                            </div>
                            <h5 class="lh-base mb-0">{{ $highlight['label'] ?? '' }}</h5>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if(($settings['about.visible'] ?? '1') === '1')
    <div id="about" class="container-fluid py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6 wow fadeIn" data-wow-delay="0.1s">
                            <img class="img-fluid" src="{{ $resolveMedia($settings['about.image_primary'] ?? null, $assetBase('img/about-1.jpg')) }}" alt="{{ $settings['about.heading'] ?? 'About Us' }}">
                        </div>
                        <div class="col-6 wow fadeIn" data-wow-delay="0.3s">
                            <img class="img-fluid h-75" src="{{ $resolveMedia($settings['about.image_secondary'] ?? null, $assetBase('img/about-2.jpg')) }}" alt="{{ $settings['about.heading'] ?? 'About Us' }}">
                            <div class="h-25 d-flex align-items-center text-center bg-primary px-4">
                                <h4 class="text-white lh-base mb-0">{{ $settings['about.badge_text'] ?? 'Award Winning Studio Since 1990' }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                    <h1 class="mb-5">{!! $settings['about.heading'] ?? '<span class="text-uppercase text-primary bg-light px-2">History</span> of Our Creation' !!}</h1>
                    <p class="mb-4">{{ $settings['about.text_primary'] ?? 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum et tempor sit. Aliqu diam amet diam et eos labore.' }}</p>
                    <p class="mb-5">{{ $settings['about.text_secondary'] ?? 'Clita erat ipsum et lorem et sit, sed stet no labore lorem sit. Sanctus clita duo justo et tempor.' }}</p>
                    @php
                        $checklist = $normalizeChecklist(json_decode($settings['about.checklist'] ?? '[]', true));
                        if ($checklist === []) {
                            $checklist = ['Award Winning', 'Professional Staff', '24/7 Support', 'Fair Prices'];
                        }
                    @endphp
                    <div class="row g-3">
                        @foreach($checklist as $item)
                            <div class="col-sm-6">
                                <h6 class="mb-3"><i class="fa fa-check text-primary me-2"></i>{{ $item }}</h6>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['features.visible'] ?? '1') === '1')
    <div id="features" class="container-fluid py-5">
        <div class="container">
            <div class="text-center wow fadeIn" data-wow-delay="0.1s">
                <h1 class="mb-5">{!! $settings['features.heading'] ?? 'Why People <span class="text-uppercase text-primary bg-light px-2">Choose Us</span>' !!}</h1>
            </div>
            <div class="row g-5 align-items-center text-center">
                @foreach ($featureItems as $item)
                    <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.{{ ($loop->iteration % 3) * 2 + 1 }}s">
                        <i class="{{ $item['icon'] ?? 'fa fa-star' }} fa-5x text-primary mb-4"></i>
                        <h4>{{ $item['title'] ?? '' }}</h4>
                        <p class="mb-0">{{ $item['text'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if(($settings['projects.visible'] ?? '1') === '1')
    <div id="projects" class="container-fluid mt-5">
        <div class="container mt-5">
            <div class="row g-0">
                <div class="col-lg-5 wow fadeIn" data-wow-delay="0.1s">
                    <div class="d-flex flex-column justify-content-center bg-primary h-100 p-5">
                        <h1 class="text-white mb-5">{!! $settings['projects.heading'] ?? 'Our Latest <span class="text-uppercase text-primary bg-light px-2">Projects</span>' !!}</h1>
                        <h4 class="text-white mb-0">{{ $settings['projects.subheading'] ?? '6 of our latest projects' }}</h4>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="row g-0">
                        @foreach ($projectItems as $item)
                            <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.{{ $loop->iteration + 1 }}s">
                                <div class="project-item position-relative overflow-hidden">
                                    <img class="img-fluid w-100" src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                    <a class="project-overlay text-decoration-none" href="{{ $item['link'] ?? '#!' }}">
                                        <h4 class="text-white">{{ $item['title'] }}</h4>
                                        @if(!empty($item['count']))
                                            <small class="text-white">{{ $item['count'] }}</small>
                                        @endif
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['products.visible'] ?? '1') === '1')
    <div id="products" class="container-fluid py-5 bg-light">
        <div class="container py-5">
            <div class="text-center wow fadeIn" data-wow-delay="0.1s">
                <h1 class="mb-5">{{ $settings['products.heading'] ?? 'Produk Unggulan' }}</h1>
            </div>
            <div class="row g-4">
                @foreach ($products as $product)
                    @php
                        $imagePath = $product->image_url ?? optional($product->images()->first())->path;
                        $imageUrl = $imagePath ? asset('storage/' . $imagePath) : $assetBase('img/project-1.jpg');
                        $promoPrice = $product->promo_price;
                        $finalPrice = $product->final_price;
                        $basePrice = $product->price;
                        $displayPromotion = $product->currentPromotion(null, null, false);
                        $promoLabel = $displayPromotion?->label;
                        $audienceLabel = $displayPromotion?->audience_label;
                        $eligiblePromoLabel = $product->promo_label;
                        $hasPromo = $promoPrice !== null && $eligiblePromoLabel;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="position-relative">
                                <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $product->name }}">
                                @if($promoLabel)
                                    <span class="promo-label position-absolute top-0 start-0 m-3">{{ $promoLabel }}</span>
                                @endif
                                @if($audienceLabel)
                                    <span class="promo-label position-absolute top-0 end-0 m-3">{{ $audienceLabel }}</span>
                                @endif
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text text-muted flex-grow-1">{{ Str::limit($product->description, 120) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="price-stack">
                                        @if($hasPromo)
                                            <span class="price-original">Rp {{ $formatPrice($basePrice) }}</span>
                                            <span class="price-current text-primary">Rp {{ $formatPrice($promoPrice) }}</span>
                                        @else
                                            <span class="price-current text-primary">Rp {{ $formatPrice($finalPrice) }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-primary">Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if(($settings['services.visible'] ?? '1') === '1')
    <div id="services" class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 wow fadeIn" data-wow-delay="0.1s">
                    <h1 class="mb-4">{!! $settings['services.heading'] ?? 'Our Creative <span class="text-uppercase text-primary bg-light px-2">Services</span>' !!}</h1>
                    <p>{{ $settings['services.description'] ?? 'Aliqu diam amet diam et eos labore. Clita erat ipsum et lorem et sit, sed stet no labore lorem sit.' }}</p>
                    <p class="mb-5">{{ $settings['services.description_secondary'] ?? 'Tempor erat elitr rebum at clita. Diam dolor diam ipsum et tempor sit.' }}</p>
                    <div class="d-flex align-items-center bg-light">
                        <div class="btn-square flex-shrink-0 bg-primary" style="width: 100px; height: 100px;">
                            <i class="fa fa-phone fa-2x text-white"></i>
                        </div>
                        <div class="px-3">
                            <h3>{{ $settings['services.phone'] ?? '+62 812-3456-7890' }}</h3>
                            <span>{{ $settings['services.phone_label'] ?? 'Hubungi kami untuk konsultasi gratis' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="row g-0">
                        @foreach ($services as $service)
                            <div class="col-md-6 wow fadeIn" data-wow-delay="0.{{ ($loop->iteration % 4) * 2 + 2 }}s">
                                <div class="service-item h-100 d-flex flex-column justify-content-center {{ $loop->iteration % 2 === 1 ? 'bg-primary text-white' : 'bg-light' }}">
                                    <a href="{{ $service['link'] ?? '#!' }}" class="service-img position-relative mb-4">
                                        <img class="img-fluid w-100" src="{{ $service['image'] }}" alt="{{ $service['title'] }}">
                                        <h3>{{ $service['title'] }}</h3>
                                    </a>
                                    <p class="mb-0">{{ $service['text'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['team.visible'] ?? '1') === '1')
    <div id="team" class="container-fluid bg-light py-5">
        <div class="container py-5">
            <h1 class="mb-5">{!! $settings['team.heading'] ?? 'Our Professional <span class="text-uppercase text-primary bg-light px-2">Designers</span>' !!}</h1>
            <div class="row g-4">
                @foreach ($teamMembers as $member)
                    <div class="col-md-6 col-lg-3 wow fadeIn" data-wow-delay="0.{{ $loop->iteration }}s">
                        <div class="team-item position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="{{ $member['photo'] }}" alt="{{ $member['name'] }}">
                            <div class="team-overlay">
                                @if(!empty($member['title']))
                                    <small class="mb-2">{{ $member['title'] }}</small>
                                @endif
                                <h4 class="lh-base text-light">{{ $member['name'] }}</h4>
                                @if(!empty($member['social']))
                                    <div class="d-flex justify-content-center">
                                        @foreach ($member['social'] as $socialLink)
                                            <a class="btn btn-outline-primary btn-sm-square border-2 me-2" href="{{ $socialLink }}" target="_blank" rel="noopener">
                                                <i class="fab fa-external-link-alt"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if(($settings['testimonials.visible'] ?? '1') === '1' && count($testimonials))
    <div id="testimonials" class="container-xxl py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-9">
                    <div class="owl-carousel testimonial-carousel wow fadeIn" data-wow-delay="0.2s">
                        @foreach ($testimonials as $testimonial)
                            <div class="testimonial-item">
                                <div class="row g-5 align-items-center">
                                    <div class="col-md-6">
                                        <div class="testimonial-img">
                                            <img class="img-fluid" src="{{ $testimonial['photo'] }}" alt="{{ $testimonial['name'] }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="testimonial-text pb-5 pb-md-0">
                                            <h3>{{ $testimonial['title'] ?? '' }}</h3>
                                            <p>{{ $testimonial['text'] ?? '' }}</p>
                                            <h5 class="mb-0">{{ $testimonial['name'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['newsletter.visible'] ?? '1') === '1')
    <div id="newsletter" class="container-fluid bg-primary newsletter p-0">
        <div class="container p-0">
            <div class="row g-0 align-items-center">
                <div class="col-md-5 ps-lg-0 text-start wow fadeIn" data-wow-delay="0.2s">
                    <img class="img-fluid w-100" src="{{ $resolveMedia($settings['newsletter.image'] ?? null, $assetBase('img/newsletter.jpg')) }}" alt="Newsletter">
                </div>
                <div class="col-md-7 py-5 newsletter-text wow fadeIn" data-wow-delay="0.5s">
                    <div class="p-5">
                        <h1 class="mb-5">{!! $settings['newsletter.heading'] ?? 'Subscribe the <span class="text-uppercase text-primary bg-white px-2">Newsletter</span>' !!}</h1>
                        <div class="position-relative w-100 mb-2">
                            <input class="form-control border-0 w-100 ps-4 pe-5" type="email" placeholder="{{ $settings['newsletter.placeholder'] ?? 'Enter Your Email' }}" style="height: 60px;">
                            <button type="button" class="btn shadow-none position-absolute top-0 end-0 mt-2 me-2"><i class="fa fa-paper-plane text-primary fs-4"></i></button>
                        </div>
                        <p class="mb-0">{{ $settings['newsletter.description'] ?? 'Diam sed sed dolor stet amet eirmod.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(($settings['contact.visible'] ?? '1') === '1')
    <div id="contact" class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center wow fadeIn" data-wow-delay="0.1s">
                <h1 class="mb-5">{{ $settings['contact.heading'] ?? 'Have Any Query? <span class="text-uppercase text-primary bg-light px-2">Contact Us</span>' }}</h1>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="wow fadeIn" data-wow-delay="0.3s">
                        <form method="POST" action="{{-- {{ route('contact.submit') }} --}}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="contact-name" name="name" placeholder="Nama Anda" required>
                                        <label for="contact-name">Nama Anda</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="contact-email" name="email" placeholder="Email Anda" required>
                                        <label for="contact-email">Email Anda</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="contact-subject" name="subject" placeholder="Subjek" required>
                                        <label for="contact-subject">Subjek</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Tuliskan pesan Anda" id="contact-message" name="message" style="height: 150px" required></textarea>
                                        <label for="contact-message">Pesan</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary w-100 py-3" type="submit">Kirim Pesan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 mt-5 mt-lg-0">
                    <div class="h-100 wow fadeIn" data-wow-delay="0.5s">
                        @if(!empty($settings['contact.map']))
                            {!! $settings['contact.map'] !!}
                        @else
                            <div style="width:100%; height:100%; min-height:320px; background:#f2f2f2;"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{!! view()->file(base_path('themes/' . $themeName . '/views/components/footer.blade.php'), [
    'footer' => $footerConfig,
    'brand' => $navigation['brand'],
])->render() !!}

    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

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
</body>
</html>
