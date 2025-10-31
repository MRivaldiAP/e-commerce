<?php

namespace App\Providers;

use App\Services\Payments\PaymentGatewayManager;
use App\Services\Shipping\ShippingGatewayManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function () {
            return new PaymentGatewayManager();
        });

        $this->app->singleton(ShippingGatewayManager::class, function () {
            return new ShippingGatewayManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(database_path('migrations/tenant'));
        }
    }
}
