<?php

use App\Models\Setting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThemeAssetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

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

Route::get('/', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/home.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
});

Route::get('/produk', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/product.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
})->name('products.index');

Route::get('/tentang-kami', function () {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/tentang-kami.blade.php");
    if (File::exists($viewPath)) {
        return view()->file($viewPath, ['theme' => $activeTheme]);
    }
    abort(404);
})->name('about');

Route::get('/produk/{product}', function (Product $product) {
    $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
    $viewPath = base_path("themes/{$activeTheme}/views/product-detail.blade.php");
    if (File::exists($viewPath)) {
        $product->load([
            'images',
            'categories',
            'comments' => fn ($query) => $query->where('is_active', true)->latest(),
        ]);

        return view()->file($viewPath, [
            'theme' => $activeTheme,
            'product' => $product,
        ]);
    }
    abort(404);
})->name('products.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.items.destroy');

Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');

Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/checkout/payment/session', [CheckoutController::class, 'createPaymentSession'])->name('checkout.payment.session');
Route::post('/checkout/payment/webhook/{gateway}', [CheckoutController::class, 'webhook'])->name('checkout.payment.webhook');

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
            return view('layout.admin');
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
    });

    Route::middleware('role:' . User::ROLE_ADMINISTRATOR)->group(function () {
        Route::get('themes', [ThemeController::class, 'index'])->name('admin.themes.index');
        Route::post('themes', [ThemeController::class, 'update'])->name('admin.themes.update');
        Route::get('themes/preview/{theme}', [ThemeController::class, 'preview'])->name('admin.themes.preview');

        Route::resource('tags', TagController::class)->except(['show'])->names('admin.tags');

        Route::get('pages/home', [PageController::class, 'home'])->name('admin.pages.home');
        Route::post('pages/home', [PageController::class, 'updateHome'])->name('admin.pages.home.update');
        Route::get('pages/about', [PageController::class, 'about'])->name('admin.pages.about');
        Route::post('pages/about', [PageController::class, 'updateAbout'])->name('admin.pages.about.update');
        Route::get('pages/product', [PageController::class, 'product'])->name('admin.pages.product');
        Route::post('pages/product', [PageController::class, 'updateProduct'])->name('admin.pages.product.update');
        Route::get('pages/product-detail', [PageController::class, 'productDetail'])->name('admin.pages.product-detail');
        Route::post('pages/product-detail', [PageController::class, 'updateProductDetail'])->name('admin.pages.product-detail.update');
        Route::patch('pages/product-detail/comments/{comment}', [PageController::class, 'toggleComment'])->name('admin.pages.product-detail.comments.toggle');
        Route::get('pages/cart', [PageController::class, 'cart'])->name('admin.pages.cart');
        Route::post('pages/cart', [PageController::class, 'updateCart'])->name('admin.pages.cart.update');
        Route::get('pages/layout', [PageController::class, 'layout'])->name('admin.pages.layout');
        Route::post('pages/layout', [PageController::class, 'updateLayout'])->name('admin.pages.layout.update');

        Route::get('payments', [PaymentController::class, 'index'])->name('admin.payments.index');
        Route::post('payments', [PaymentController::class, 'update'])->name('admin.payments.update');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show'])->names('admin.users');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
