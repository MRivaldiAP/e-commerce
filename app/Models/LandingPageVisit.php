<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'date',
        'total_visits',
        'unique_visits',
        'primary_visits',
        'secondary_visits',
    ];

    protected $casts = [
        'date' => 'date',
        'total_visits' => 'integer',
        'unique_visits' => 'integer',
        'primary_visits' => 'integer',
        'secondary_visits' => 'integer',
    ];
}
