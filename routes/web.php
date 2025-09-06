<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;

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
    return view('layout.admin');
});

Route::prefix('admin')/* ->middleware(['auth']) */->group(function () {
    // products
    Route::get('products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('products-create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::patch('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    Route::post('products/bulk', [ProductController::class, 'bulk'])->name('admin.products.bulk');


    Route::post('products/{id}/stock', [ProductController::class, 'updateStock']);
    Route::post('products/{id}/images', [ProductController::class, 'uploadImages']);
    Route::get('products/{id}/images', [ProductController::class, 'listImages']);
    Route::delete('products/{productId}/images/{imageId}', [ProductController::class, 'removeImage']);
    Route::post('products/{id}/replace-images', [ProductController::class, 'replaceImages']);
    Route::put('products/{id}/categories', [ProductController::class, 'syncCategories']);

    // categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::patch('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('categories/{id}/products', [CategoryController::class, 'products']);
});