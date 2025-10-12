<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RajaOngkirCity extends Model
{
    use HasFactory;

    protected $table = 'rajaongkir_cities';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    public function province(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirProvince::class, 'province_id');
    }

    public function subdistricts(): HasMany
    {
        return $this->hasMany(RajaOngkirSubdistrict::class, 'city_id');
    }
}
