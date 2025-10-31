<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Theme extends TenantModel
{
    use HasFactory;

    protected $fillable = ['name', 'display_name'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
