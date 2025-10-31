<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AIController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryItemController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ShippingController as AdminShippingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\ArticleController as FrontArticleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ShippingLocationController;
use App\Http\Controllers\ThemeAssetController;
use App\Models\LandingPageVisit;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

return static function (): void {
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
                    abort(404);
                }

                $visitsQuery = LandingPageVisit::query()->whereBetween('visited_at', [
                    $today->copy()->startOfDay(),
                    $today->copy()->endOfDay(),
                ]);

                if ($selectedPage !== 'all') {
                    $visitsQuery->where('page', $selectedPage);
                }

                $totalVisitsToday = $visitsQuery->count();

                return view('admin.dashboard', [
                    'totalVisitsToday' => $totalVisitsToday,
                    'selectedPage' => $selectedPage,
                    'availablePages' => $availablePages,
                ]);
            })->name('admin.dashboard');

            Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
            Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

            Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
            Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');

            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::resource('tags', TagController::class)->except(['show']);

            Route::resource('promotions', PromotionController::class);

            Route::resource('articles', AdminArticleController::class);

            Route::resource('gallery-categories', GalleryCategoryController::class)->except(['show']);
            Route::resource('gallery-items', GalleryItemController::class);

            Route::resource('media-assets', MediaAssetController::class)->except(['show']);

            Route::resource('pages', PageController::class);

            Route::get('shipping/locations', [AdminShippingController::class, 'index'])->name('admin.shipping.locations.index');
            Route::post('shipping/locations/synchronize', [AdminShippingController::class, 'synchronize'])
                ->name('admin.shipping.locations.synchronize');

            Route::resource('payments', PaymentController::class)->except(['show']);

            Route::get('ai', [AIController::class, 'index'])->name('admin.ai.index');
            Route::post('ai/generate', [AIController::class, 'generate'])->name('admin.ai.generate');

            Route::get('themes', [ThemeController::class, 'index'])->name('admin.themes.index');
            Route::post('themes/activate', [ThemeController::class, 'activate'])->name('admin.themes.activate');

            Route::get('settings/page', [PageController::class, 'editSettings'])->name('admin.pages.settings.edit');
            Route::post('settings/page', [PageController::class, 'updateSettings'])->name('admin.pages.settings.update');
        });

        Route::middleware('role:' . implode(',', [
            User::ROLE_ADMINISTRATOR,
            User::ROLE_PRODUCT_MANAGER,
        ]))->group(function () {
            Route::post('/products/{product}/restore', [ProductController::class, 'restore'])->name('admin.products.restore');
        });

        Route::middleware('role:' . implode(',', [
            User::ROLE_ADMINISTRATOR,
            User::ROLE_PRODUCT_MANAGER,
            User::ROLE_ORDER_MANAGER,
        ]))->group(function () {
            Route::get('/orders/export', [AdminOrderController::class, 'export'])->name('admin.orders.export');
        });

        Route::middleware('role:' . implode(',', [
            User::ROLE_ADMINISTRATOR,
            User::ROLE_PRODUCT_MANAGER,
            User::ROLE_MARKETING_MANAGER,
        ]))->group(function () {
            Route::get('/promotions/export', [PromotionController::class, 'export'])->name('admin.promotions.export');
        });
    });

    Route::get('/admin-login', function () {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    })->name('admin.login');

    Route::post('/admin-login', function () {
        $credentials = request()->only('email', 'password');

        if (Auth::attempt($credentials, request()->boolean('remember'))) {
            request()->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    })->name('admin.login.attempt');

    Route::post('/admin-logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    })->name('admin.logout');

    Route::post('/admin/password/reset-link', [\App\Http\Controllers\Auth\AdminForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('guest')
        ->name('admin.password.email');

    Route::post('/admin/password/reset', [\App\Http\Controllers\Auth\AdminResetPasswordController::class, 'reset'])
        ->middleware('guest')
        ->name('admin.password.update');

    Route::get('/admin/password/reset/{token}', [\App\Http\Controllers\Auth\AdminResetPasswordController::class, 'showResetForm'])
        ->middleware('guest')
        ->name('admin.password.reset');
};
