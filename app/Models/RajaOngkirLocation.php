<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RajaOngkirLocation extends Model
{
    protected $fillable = [
        'level',
        'remote_id',
        'name',
        'type',
        'province',
        'province_code',
        'city',
        'city_code',
        'nusa_regency_code',
        'nusa_district_code',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function label(): string
    {
        $parts = [];

        if ($this->level === 'subdistrict') {
            if ($this->name !== '') {
                $parts[] = $this->name;
            }
            if ($this->city !== '') {
                $parts[] = $this->city;
            }
        } else {
            $type = $this->type ? strtoupper($this->type) : null;
            if ($type) {
                $parts[] = $type;
            }
            if ($this->name !== '') {
                $parts[] = $this->name;
            }
        }

        if ($this->province !== '') {
            $parts[] = $this->province;
        }

        return implode(', ', array_filter($parts, fn ($value) => $value !== null && $value !== ''));
    }
}
