<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RajaOngkirCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'province_id',
        'province',
        'type',
        'city_name',
        'postal_code',
        'nusa_regency_code',
        'normalized_slug',
        'province_slug',
    ];

    public $timestamps = false;
}
