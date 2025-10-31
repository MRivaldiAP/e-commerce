<?php

namespace App\Models;


use App\Models\TenantModel;
class Setting extends TenantModel
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

