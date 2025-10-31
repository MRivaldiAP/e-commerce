<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\PreventAccessFromTenantDomains;

$applicationRoutes = require __DIR__ . '/application.php';

Route::middleware(PreventAccessFromTenantDomains::class)->group(function () use ($applicationRoutes): void {
    $applicationRoutes();

    Route::view('/central', 'welcome')->name('central.home');
});
