<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RajaOngkirSubdistrict extends Model
{
    use HasFactory;

    protected $table = 'rajaongkir_subdistricts';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    public function city(): BelongsTo
    {
        return $this->belongsTo(RajaOngkirCity::class, 'city_id');
    }
}
