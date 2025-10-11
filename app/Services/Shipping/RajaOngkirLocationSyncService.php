<?php

namespace App\Services\Shipping;

use App\Models\RajaOngkirLocation;
use App\Services\Shipping\Exceptions\ShippingException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RajaOngkirLocationSyncService
{
    /**
     * @param  array<string, mixed>  $config
     * @return array{cities: int, subdistricts: int}
     */
    public function sync(array $config): array
    {
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        if ($apiKey === '') {
            throw new ShippingException('API key RajaOngkir belum dikonfigurasi.');
        }

        $accountType = (string) ($config['account_type'] ?? 'starter');
        $cities = $this->syncCities($apiKey, $accountType);
        $subdistricts = 0;

        if ($accountType === 'pro') {
            $subdistricts = $this->syncSubdistricts($apiKey, $accountType);
        } else {
            RajaOngkirLocation::subdistricts()->delete();
        }

        return [
            'cities' => $cities,
            'subdistricts' => $subdistricts,
        ];
    }

    protected function syncCities(string $apiKey, string $accountType): int
    {
        $response = Http::withHeaders(['key' => $apiKey])->get($this->baseUrl($accountType).'/city');

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description') ?: $response->reason();
            throw new ShippingException($message ?: 'Gagal mengambil daftar kota dari RajaOngkir.');
        }

        $results = Arr::get($response->json(), 'rajaongkir.results', []);
        $results = is_array($results) ? $results : [];

        $ids = [];

        DB::transaction(function () use ($results, &$ids) {
            foreach ($results as $city) {
                $cityId = (string) Arr::get($city, 'city_id');
                if ($cityId === '') {
                    continue;
                }

                $cityName = (string) Arr::get($city, 'city_name', '');
                $type = (string) Arr::get($city, 'type', '');
                $slug = RajaOngkirLocation::normalizeName($cityName);
                $fullSlug = RajaOngkirLocation::fullSlug($type, $cityName);

                $province = (string) Arr::get($city, 'province', '');
                $provinceSlug = RajaOngkirLocation::normalizeProvince($province);

                RajaOngkirLocation::updateOrCreate(
                    [
                        'location_type' => 'city',
                        'external_id' => $cityId,
                    ],
                    [
                        'name' => $cityName,
                        'slug' => $slug,
                        'full_slug' => $fullSlug,
                        'type' => (string) Arr::get($city, 'type', ''),
                        'province' => $province,
                        'province_slug' => $provinceSlug,
                        'province_id' => (string) Arr::get($city, 'province_id', ''),
                        'postal_code' => (string) Arr::get($city, 'postal_code', ''),
                        'meta' => $city,
                    ]
                );

                $ids[] = $cityId;
            }

            if (! empty($ids)) {
                RajaOngkirLocation::cities()->whereNotIn('external_id', $ids)->delete();
            }
        });

        return count($ids);
    }

    protected function syncSubdistricts(string $apiKey, string $accountType): int
    {
        $cities = RajaOngkirLocation::cities()->get(['external_id', 'name', 'slug']);
        $syncedIds = [];
        $baseUrl = $this->baseUrl($accountType);

        foreach ($cities as $city) {
            $response = Http::withHeaders(['key' => $apiKey])->get($baseUrl.'/subdistrict', [
                'city' => $city->external_id,
            ]);

            if ($response->failed()) {
                $message = Arr::get($response->json(), 'rajaongkir.status.description') ?: $response->reason();
                throw new ShippingException($message ?: sprintf('Gagal mengambil kecamatan untuk kota %s.', $city->name));
            }

            $results = Arr::get($response->json(), 'rajaongkir.results', []);
            $results = is_array($results) ? $results : [];

            DB::transaction(function () use ($results, $city, &$syncedIds) {
                foreach ($results as $subdistrict) {
                    $subdistrictId = (string) Arr::get($subdistrict, 'subdistrict_id');
                    if ($subdistrictId === '') {
                        continue;
                    }

                    $name = (string) Arr::get($subdistrict, 'subdistrict_name', '');
                    $slug = RajaOngkirLocation::normalizeName($name);
                    $fullSlug = RajaOngkirLocation::fullSlug((string) Arr::get($subdistrict, 'type', ''), $name);
                    $province = (string) Arr::get($subdistrict, 'province', '');
                    $provinceSlug = RajaOngkirLocation::normalizeProvince($province);
                    $citySlug = RajaOngkirLocation::normalizeName((string) Arr::get($subdistrict, 'city', $city->name));

                    RajaOngkirLocation::updateOrCreate(
                        [
                            'location_type' => 'subdistrict',
                            'external_id' => $subdistrictId,
                        ],
                        [
                            'name' => $name,
                            'slug' => $slug,
                            'full_slug' => $fullSlug,
                            'type' => (string) Arr::get($subdistrict, 'type', ''),
                            'province' => $province,
                            'province_slug' => $provinceSlug,
                            'province_id' => (string) Arr::get($subdistrict, 'province_id', ''),
                            'city_name' => (string) Arr::get($subdistrict, 'city', $city->name),
                            'city_slug' => $citySlug,
                            'city_external_id' => (string) Arr::get($subdistrict, 'city_id', $city->external_id),
                            'postal_code' => (string) Arr::get($subdistrict, 'postal_code', ''),
                            'meta' => $subdistrict,
                        ]
                    );

                    $syncedIds[] = $subdistrictId;
                }
            });
        }

        if (! empty($syncedIds)) {
            RajaOngkirLocation::subdistricts()->whereNotIn('external_id', $syncedIds)->delete();
        } else {
            RajaOngkirLocation::subdistricts()->delete();
        }

        return count($syncedIds);
    }

    protected function baseUrl(string $accountType): string
    {
        return match ($accountType) {
            'basic' => 'https://api.rajaongkir.com/basic',
            'pro' => 'https://pro.rajaongkir.com/api',
            default => 'https://api.rajaongkir.com/starter',
        };
    }
}
