<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $location_type
 * @property string $external_id
 * @property string $name
 * @property string|null $province
 * @property string|null $city_name
 */
class RajaOngkirLocation extends Model
{
    protected $fillable = [
        'location_type',
        'external_id',
        'name',
        'slug',
        'full_slug',
        'type',
        'province',
        'province_slug',
        'province_id',
        'city_name',
        'city_slug',
        'city_external_id',
        'postal_code',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function scopeCities(Builder $query): Builder
    {
        return $query->where('location_type', 'city');
    }

    public function scopeSubdistricts(Builder $query): Builder
    {
        return $query->where('location_type', 'subdistrict');
    }

    public static function normalizeName(string $value): string
    {
        $value = Str::lower($value);
        $value = str_replace([
            'kab.',
            'kabupaten',
            'kota',
            'adm.',
            'administrasi',
        ], '', $value);

        $normalized = preg_replace('/[^a-z0-9]/', '', $value);

        return is_string($normalized) ? $normalized : '';
    }

    public static function fullSlug(string $type, string $name): string
    {
        $value = trim(sprintf('%s %s', $type, $name));
        $value = Str::lower($value);

        $normalized = preg_replace('/[^a-z0-9]/', '', $value);

        return is_string($normalized) ? $normalized : '';
    }

    public static function fullSlugFromName(string $value): string
    {
        $value = Str::lower($value);

        $normalized = preg_replace('/[^a-z0-9]/', '', $value);

        return is_string($normalized) ? $normalized : '';
    }

    public static function normalizeProvince(string $value): string
    {
        $value = Str::lower($value);
        $value = str_replace([
            'provinsi',
            'propinsi',
            'province',
            'prov.',
            'prov',
        ], '', $value);

        $normalized = preg_replace('/[^a-z0-9]/', '', $value) ?: '';

        $aliases = [
            'daerahistimewayogyakarta' => 'diyogyakarta',
            'istimewayogyakarta' => 'diyogyakarta',
            'yogyakarta' => 'diyogyakarta',
            'daerahkhususibukotajakarta' => 'dkijakarta',
            'khususibukotajakarta' => 'dkijakarta',
            'ibukotajakarta' => 'dkijakarta',
            'jakarta' => 'dkijakarta',
            'bangkabelitung' => 'kepulauanbangkabelitung',
            'bangkabelitungislands' => 'kepulauanbangkabelitung',
            'riauislands' => 'kepulauanriau',
        ];

        return $aliases[$normalized] ?? $normalized;
    }
}
