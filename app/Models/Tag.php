<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends TenantModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function themes()
    {
        return $this->belongsToMany(Theme::class);
    }
}
