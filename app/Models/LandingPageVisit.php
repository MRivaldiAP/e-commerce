<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandingPageVisit extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'page',
        'visit_date',
        'total_visits',
        'unique_visits',
        'primary_visits',
        'secondary_visits',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'total_visits' => 'integer',
        'unique_visits' => 'integer',
        'primary_visits' => 'integer',
        'secondary_visits' => 'integer',
    ];
}
