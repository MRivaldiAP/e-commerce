<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;

$applicationRoutes = require __DIR__ . '/application.php';


foreach (config('tenancy.central_domains') as $domain) { Route::domain($domain)->group(function () use($applicationRoutes) :void{
        $applicationRoutes();
    });
}