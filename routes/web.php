<?php

use App\Models\Setting;
use App\Models\Product;
use App\Models\LandingPageVisit;
use App\Models\PageSetting;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThemeAssetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\AIController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\ArticleController as FrontArticleController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ShippingLocationController;
use App\Http\Controllers\Admin\ShippingController as AdminShippingController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryItemController;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('track.landing.page')->get('/', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/home.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
})->name('home')->middleware('track.landing.page');

Route::get('/produk', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/product.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
})->name('products.index')->middleware('track.landing.page');

Route::get('/tentang-kami', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/tentang-kami.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
})->name('about')->middleware('track.landing.page');

Route::get('/kontak', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/kontak.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
})->name('contact')->middleware('track.landing.page');

Route::get('/produk/{product:slug}', function (Product $product) {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/product-detail.blade.php");
        if (File::exists($viewPath)) {
            $product->load([
                'images',
                'categories',
                'promotions',
                'comments' => fn ($query) => $query->where('is_active', true)->latest(),
            ]);

        return view()->file($viewPath, [
            'theme' => $activeTheme,
            'product' => $product,
        ]);
    }
    abort(404);
})->name('products.show')->middleware('track.landing.page');

Route::get('/artikel', [FrontArticleController::class, 'index'])->name('articles.index')->middleware('track.landing.page');
Route::get('/artikel/{slug}', [FrontArticleController::class, 'show'])->name('articles.show')->middleware('track.landing.page');
Route::get('/galeri', [GalleryController::class, 'index'])->name('gallery.index')->middleware('track.landing.page');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index')->middleware('track.landing.page');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.items.destroy');

Route::get('/checkout/shipping', [ShippingController::class, 'index'])->name('checkout.shipping');
Route::post('/checkout/shipping', [ShippingController::class, 'store'])->name('checkout.shipping.store');
Route::post('/checkout/shipping/quote', [ShippingController::class, 'quote'])->name('checkout.shipping.quote');
Route::post('/shipping/track', [ShippingController::class, 'track'])->name('shipping.track');

Route::get('/shipping/locations/regencies', [ShippingLocationController::class, 'regencies'])->name('shipping.locations.regencies');
Route::get('/shipping/locations/districts', [ShippingLocationController::class, 'districts'])->name('shipping.locations.districts');
Route::get('/shipping/locations/villages', [ShippingLocationController::class, 'villages'])->name('shipping.locations.villages');
Route::get('/shipping/locations/villages/{code}', [ShippingLocationController::class, 'village'])->name('shipping.locations.village');

Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');

Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/checkout/payment/session', [CheckoutController::class, 'createPaymentSession'])->name('checkout.payment.session');
Route::post('/checkout/payment/webhook/{gateway}', [CheckoutController::class, 'webhook'])->name('checkout.payment.webhook');
Route::get('/checkout/shipping', [ShippingController::class, 'index'])->name('checkout.shipping');
Route::post('/checkout/shipping', [ShippingController::class, 'store'])->name('checkout.shipping.store');
Route::post('/checkout/shipping/rates', [ShippingController::class, 'rates'])->name('checkout.shipping.rates');
Route::delete('/checkout/shipping', [ShippingController::class, 'destroy'])->name('checkout.shipping.destroy');

Route::get('themes/{theme}/assets/{path}', ThemeAssetController::class)
    ->where('path', '.*')
    ->name('themes.assets');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::middleware('role:' . implode(',', [
        User::ROLE_ADMINISTRATOR,
        User::ROLE_PRODUCT_MANAGER,
        User::ROLE_ORDER_MANAGER,
    ]))->group(function () {
        Route::get('/', function () {
            $selectedPage = request('page', 'all');
            $today = Carbon::today();

            $availablePages = LandingPageVisit::query()
                ->select('page')
                ->distinct()
                ->orderBy('page')
                ->pluck('page')
                ->toArray();

            if ($selectedPage !== 'all' && ! in_array($selectedPage, $availablePages, true)) {
                $selectedPage = 'all';
            }

            $fromInput = request('from_date');
            $toInput = request('to_date');

            $toDate = $toInput && Carbon::hasFormat($toInput, 'Y-m-d')
                ? Carbon::createFromFormat('Y-m-d', $toInput)
                : $today->copy();

            $fromDate = $fromInput && Carbon::hasFormat($fromInput, 'Y-m-d')
                ? Carbon::createFromFormat('Y-m-d', $fromInput)
                : $toDate->copy()->subDays(6);

            if ($fromDate->greaterThan($toDate)) {
                [$fromDate, $toDate] = [$toDate->copy(), $fromDate->copy()];
            }

            $fromDate = $fromDate->startOfDay();
            $toDate = $toDate->endOfDay();

            $baseQuery = LandingPageVisit::query()
                ->whereBetween('visit_date', [$fromDate->toDateString(), $toDate->toDateString()]);

            if ($selectedPage !== 'all') {
                $baseQuery->where('page', $selectedPage);
            }

            $groupedVisits = $baseQuery
                ->selectRaw(<<<'SQL'
                    visit_date,
                    SUM(total_visits) as total_visits,
                    SUM(unique_visits) as unique_visits,
                    SUM(primary_visits) as primary_visits,
                    SUM(secondary_visits) as secondary_visits
                SQL
                )
                ->groupBy('visit_date')
                ->orderBy('visit_date')
                ->get()
                ->keyBy(fn ($row) => Carbon::parse($row->visit_date)->toDateString());

            $labels = [];
            $series = [
                'total' => [],
                'unique' => [],
                'primary' => [],
                'secondary' => [],
            ];

            $cursor = $fromDate->copy()->startOfDay();
            $end = $toDate->copy()->startOfDay();

            while ($cursor->lte($end)) {
                $key = $cursor->toDateString();
                $labels[] = $cursor->translatedFormat('d M Y');

                $record = $groupedVisits[$key] ?? null;

                $series['total'][] = (int) ($record->total_visits ?? 0);
                $series['unique'][] = (int) ($record->unique_visits ?? 0);
                $series['primary'][] = (int) ($record->primary_visits ?? 0);
                $series['secondary'][] = (int) ($record->secondary_visits ?? 0);

                $cursor->addDay();
            }

            return view('admin.dashboard', [
                'availablePages' => $availablePages,
                'selectedPage' => $selectedPage,
                'fromDate' => $fromDate->copy()->startOfDay(),
                'toDate' => $toDate->copy()->startOfDay(),
                'chartData' => [
                    'labels' => $labels,
                    'series' => $series,
                ],
            ]);
        })->name('admin.dashboard');
    });

    Route::middleware('role:' . implode(',', [
        User::ROLE_ADMINISTRATOR,
        User::ROLE_PRODUCT_MANAGER,
    ]))->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('products/{id}', [ProductController::class, 'show'])->name('admin.products.show');
        Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('products/{id}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::patch('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        Route::post('products/bulk', [ProductController::class, 'bulk'])->name('admin.products.bulk');

        Route::post('products/{id}/stock', [ProductController::class, 'updateStock']);
        Route::post('products/{id}/images', [ProductController::class, 'uploadImages']);
        Route::get('products/{id}/images', [ProductController::class, 'listImages']);
        Route::delete('products/{productId}/images/{imageId}', [ProductController::class, 'removeImage']);
        Route::post('products/{id}/replace-images', [ProductController::class, 'replaceImages']);
        Route::put('products/{id}/categories', [ProductController::class, 'syncCategories']);

        Route::resource('promotions', PromotionController::class)->except(['show']);

        Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('categories/{id}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('categories/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::patch('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::get('categories/{id}/products', [CategoryController::class, 'products'])->name('admin.categories.products');
    });

    Route::middleware('role:' . implode(',', [
        User::ROLE_ADMINISTRATOR,
        User::ROLE_ORDER_MANAGER,
    ]))->group(function () {
        Route::get('order', [AdminOrderController::class, 'index'])->name('admin.orders.index');
        Route::patch('order/{order}/review', [AdminOrderController::class, 'toggleReview'])->name('admin.orders.review');
        Route::patch('order/{order}/shipping', [AdminOrderController::class, 'updateShipping'])->name('admin.orders.shipping');
        Route::post('order/{order}/shipping/order', [AdminOrderController::class, 'createShippingOrder'])->name('admin.orders.shipping.order');
    });

    Route::middleware('role:' . User::ROLE_ADMINISTRATOR)->group(function () {
        Route::get('themes', [ThemeController::class, 'index'])->name('admin.themes.index');
        Route::post('themes', [ThemeController::class, 'update'])->name('admin.themes.update');
        Route::get('themes/preview/{theme}', [ThemeController::class, 'preview'])->name('admin.themes.preview');

        Route::resource('tags', TagController::class)->except(['show'])->names('admin.tags');
        Route::resource('articles', AdminArticleController::class)->except(['show'])->names('admin.articles');

        Route::get('ai', [AIController::class, 'index'])->name('admin.ai.index');
        Route::post('ai', [AIController::class, 'update'])->name('admin.ai.update');
        Route::post('ai/articles/generate', [AIController::class, 'generateArticle'])->name('admin.ai.articles.generate');

        Route::get('pages/home', [PageController::class, 'home'])->name('admin.pages.home');
        Route::post('pages/home', [PageController::class, 'updateHome'])->name('admin.pages.home.update');
        Route::get('pages/about', [PageController::class, 'about'])->name('admin.pages.about');
        Route::post('pages/about', [PageController::class, 'updateAbout'])->name('admin.pages.about.update');
        Route::get('pages/contact', [PageController::class, 'contact'])->name('admin.pages.contact');
        Route::post('pages/contact', [PageController::class, 'updateContact'])->name('admin.pages.contact.update');
        Route::get('pages/product', [PageController::class, 'product'])->name('admin.pages.product');
        Route::post('pages/product', [PageController::class, 'updateProduct'])->name('admin.pages.product.update');
        Route::get('pages/product-detail', [PageController::class, 'productDetail'])->name('admin.pages.product-detail');
        Route::post('pages/product-detail', [PageController::class, 'updateProductDetail'])->name('admin.pages.product-detail.update');
        Route::patch('pages/product-detail/comments/{comment}', [PageController::class, 'toggleComment'])->name('admin.pages.product-detail.comments.toggle');
        Route::get('pages/article', [PageController::class, 'article'])->name('admin.pages.article');
        Route::post('pages/article', [PageController::class, 'updateArticle'])->name('admin.pages.article.update');
        Route::get('pages/article-detail', [PageController::class, 'articleDetail'])->name('admin.pages.article-detail');
        Route::post('pages/article-detail', [PageController::class, 'updateArticleDetail'])->name('admin.pages.article-detail.update');
        Route::get('pages/gallery', [PageController::class, 'gallery'])->name('admin.pages.gallery');
        Route::post('pages/gallery', [PageController::class, 'updateGallery'])->name('admin.pages.gallery.update');
        Route::get('pages/cart', [PageController::class, 'cart'])->name('admin.pages.cart');
        Route::post('pages/cart', [PageController::class, 'updateCart'])->name('admin.pages.cart.update');
        Route::get('pages/layout', [PageController::class, 'layout'])->name('admin.pages.layout');
        Route::post('pages/layout', [PageController::class, 'updateLayout'])->name('admin.pages.layout.update');

        Route::resource('gallery/categories', GalleryCategoryController::class)
            ->except(['show'])
            ->names('admin.gallery.categories');
        Route::resource('gallery/items', GalleryItemController::class)
            ->except(['show'])
            ->names('admin.gallery.items');

        Route::resource('media', MediaAssetController::class)
            ->only(['index', 'store', 'destroy'])
            ->names('admin.media');

        Route::get('payments', [PaymentController::class, 'index'])->name('admin.payments.index');
        Route::post('payments', [PaymentController::class, 'update'])->name('admin.payments.update');

        Route::get('shipping', [AdminShippingController::class, 'index'])->name('admin.shipping.index');
        Route::post('shipping', [AdminShippingController::class, 'update'])->name('admin.shipping.update');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show'])->names('admin.users');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
