<?php

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThemeAssetController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PageController;

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

Route::get('themes/{theme}/assets/{path}', ThemeAssetController::class)
    ->where('path', '.*')
    ->name('themes.assets');

Route::prefix('admin')/* ->middleware(['auth']) */->group(function () {
    Route::get('/', function () {
        return view('layout.admin');
    });

    // products
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

    // categories
    Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('categories/{id}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('categories/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::patch('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('categories/{id}/products', [CategoryController::class, 'products'])->name('admin.categories.products');

    // themes
    Route::get('themes', [ThemeController::class, 'index'])->name('admin.themes.index');
    Route::post('themes', [ThemeController::class, 'update'])->name('admin.themes.update');

    Route::get('pages/home', [PageController::class, 'home'])->name('admin.pages.home');
    Route::post('pages/home', [PageController::class, 'updateHome'])->name('admin.pages.home.update');
});
