<?php

declare(strict_types=1);

use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('central.home');

Route::middleware('central.domain')
    ->prefix('admin')
    ->name('central.admin.')
    ->group(function () {
        Route::redirect('/', '/admin/tenants')->name('dashboard');

        Route::resource('tenants', TenantController::class)->except(['show']);
    });
