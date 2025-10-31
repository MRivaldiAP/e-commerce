<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Tenancy;

trait UsesApplicationConnection
{
    public function getConnectionName(): ?string
    {
        if ($this->isTenantContext()) {
            return Config::get('tenancy.database.tenant_connection', 'tenant');
        }

        return Config::get('tenancy.database.central_connection', Config::get('database.default'));
    }

    protected function isTenantContext(): bool
    {
        if (function_exists('tenant') && tenant()) {
            return true;
        }

        if (app()->bound(Tenancy::class)) {
            $tenancy = app(Tenancy::class);

            return $tenancy->initialized && $tenancy->tenant instanceof Tenant;
        }

        return false;
    }
}
