<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RajaOngkirSubdistrict extends Model
{
    use HasFactory;

    protected $fillable = [
        'subdistrict_id',
        'city_id',
        'city',
        'province_id',
        'province',
        'type',
        'subdistrict_name',
        'nusa_district_code',
        'normalized_slug',
        'province_slug',
        'city_slug',
    ];

    public $timestamps = false;
}
