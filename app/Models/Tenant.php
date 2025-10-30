<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'data',
        'name',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function setNameAttribute(string $value): void
    {
        $data = $this->data ?? [];
        $data['name'] = $value;

        $this->data = $data;
    }

    public function getPrimaryDomainAttribute(): ?string
    {
        return $this->domains->first()->domain ?? null;
    }
}