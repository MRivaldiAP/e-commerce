<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RajaOngkirProvince extends Model
{
    use HasFactory;

    protected $table = 'rajaongkir_provinces';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    public function cities(): HasMany
    {
        return $this->hasMany(RajaOngkirCity::class, 'province_id');
    }
}
