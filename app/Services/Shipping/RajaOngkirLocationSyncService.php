<?php

namespace App\Services\Shipping;

use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirSubdistrict;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RajaOngkirLocationSyncService
{
    public function sync(array $config): void
    {
        if (empty($config['api_key'])) {
            return;
        }

        $cities = $this->syncCities($config);

        if (($config['account_type'] ?? 'starter') === 'pro') {
            $this->syncSubdistricts($config, $cities);
        }
    }

    public function syncCities(array $config): Collection
    {
        $results = collect();

        try {
            $response = Http::withHeaders([
                'key' => $config['api_key'],
            ])->get($this->baseUrl($config).'/city');

            if ($response->failed()) {
                Log::warning('RajaOngkir city sync failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return RajaOngkirCity::query()->get();
            }

            $results = collect(Arr::get($response->json(), 'rajaongkir.results', []));
        } catch (Throwable $exception) {
            Log::warning('Unable to sync RajaOngkir cities', [
                'message' => $exception->getMessage(),
            ]);

            return RajaOngkirCity::query()->get();
        }

        $provinceIndex = $this->buildProvinceIndex();
        $regencyIndex = $this->buildRegencyIndex($provinceIndex);

        $payloads = [];
        $cityIds = [];

        foreach ($results as $city) {
            $cityId = (string) Arr::get($city, 'city_id');
            $provinceId = (string) Arr::get($city, 'province_id');
            $provinceName = (string) Arr::get($city, 'province');
            $type = (string) Arr::get($city, 'type');
            $cityName = (string) Arr::get($city, 'city_name');
            $postalCode = Arr::get($city, 'postal_code');

            $provinceSlug = $this->normalizeProvinceName($provinceName);
            $citySlug = $this->normalizeName(trim(sprintf('%s %s', $type, $cityName)));
            $cityNameSlug = $this->normalizeName($cityName);

            $nusaRegencyCode = $this->matchRegencyCode(
                $provinceSlug,
                $citySlug,
                $cityNameSlug,
                $regencyIndex
            );

            $payloads[] = [
                'city_id' => $cityId,
                'province_id' => $provinceId,
                'province' => $provinceName,
                'type' => $type,
                'city_name' => $cityName,
                'postal_code' => $postalCode ? (string) $postalCode : null,
                'nusa_regency_code' => $nusaRegencyCode,
                'normalized_slug' => $citySlug,
                'province_slug' => $provinceSlug,
            ];

            $cityIds[] = $cityId;
        }

        if (! empty($payloads)) {
            RajaOngkirCity::upsert(
                $payloads,
                ['city_id'],
                [
                    'province_id',
                    'province',
                    'type',
                    'city_name',
                    'postal_code',
                    'nusa_regency_code',
                    'normalized_slug',
                    'province_slug',
                ]
            );

            RajaOngkirCity::query()->whereNotIn('city_id', $cityIds)->delete();
            Cache::forget('shipping.rajaongkir.cities');
        }

        return RajaOngkirCity::query()->orderBy('province')->orderBy('city_name')->get();
    }

    public function syncSubdistricts(array $config, ?Collection $cities = null): void
    {
        $cities = $cities ? $cities->keyBy('city_id') : RajaOngkirCity::query()->get()->keyBy('city_id');
        if ($cities->isEmpty()) {
            return;
        }

        $districtIndex = $this->buildDistrictIndex();

        $syncedIds = [];

        foreach ($cities as $city) {
            try {
                $response = Http::withHeaders([
                    'key' => $config['api_key'],
                ])->get($this->baseUrl($config).'/subdistrict', [
                    'city' => $city->city_id,
                ]);

                if ($response->failed()) {
                    Log::warning('RajaOngkir subdistrict sync failed', [
                        'status' => $response->status(),
                        'city_id' => $city->city_id,
                        'body' => $response->json(),
                    ]);
                    continue;
                }

                $results = Arr::get($response->json(), 'rajaongkir.results', []);
            } catch (Throwable $exception) {
                Log::warning('Unable to sync RajaOngkir subdistricts', [
                    'city_id' => $city->city_id,
                    'message' => $exception->getMessage(),
                ]);
                continue;
            }

            $payloads = [];

            foreach ($results as $subdistrict) {
                $subdistrictId = (string) Arr::get($subdistrict, 'subdistrict_id');
                $provinceName = (string) Arr::get($subdistrict, 'province');
                $cityName = (string) Arr::get($subdistrict, 'city');
                $type = (string) Arr::get($subdistrict, 'type');
                $subdistrictName = (string) Arr::get($subdistrict, 'subdistrict_name');

                $provinceSlug = $this->normalizeProvinceName($provinceName);
                $citySlug = $this->normalizeName(trim(sprintf('%s %s', $type, $cityName)));
                $subdistrictSlug = $this->normalizeName(trim(sprintf('%s %s', $type, $subdistrictName)));
                $subdistrictNameSlug = $this->normalizeName($subdistrictName);

                $nusaDistrictCode = $this->matchDistrictCode(
                    $city->nusa_regency_code,
                    $subdistrictSlug,
                    $subdistrictNameSlug,
                    $districtIndex
                );

                $payloads[] = [
                    'subdistrict_id' => $subdistrictId,
                    'city_id' => (string) Arr::get($subdistrict, 'city_id'),
                    'city' => $cityName,
                    'province_id' => (string) Arr::get($subdistrict, 'province_id'),
                    'province' => $provinceName,
                    'type' => $type,
                    'subdistrict_name' => $subdistrictName,
                    'nusa_district_code' => $nusaDistrictCode,
                    'normalized_slug' => $subdistrictSlug,
                    'province_slug' => $provinceSlug,
                    'city_slug' => $citySlug,
                ];

                $syncedIds[] = $subdistrictId;
            }

            if (! empty($payloads)) {
                RajaOngkirSubdistrict::upsert(
                    $payloads,
                    ['subdistrict_id'],
                    [
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
                    ]
                );
            }
        }

        if (! empty($syncedIds)) {
            RajaOngkirSubdistrict::query()->whereNotIn('subdistrict_id', $syncedIds)->delete();
            Cache::forget('shipping.rajaongkir.subdistricts');
        }
    }

    protected function buildProvinceIndex(): array
    {
        $index = [];

        Province::query()->orderBy('code')->each(function (Province $province) use (&$index) {
            $slug = $this->normalizeProvinceName($province->name);
            if ($slug !== '') {
                $index[$slug] = $province->code;
            }
        });

        return $index;
    }

    protected function buildRegencyIndex(array $provinceIndex): array
    {
        $index = [];

        Regency::query()->orderBy('province_code')->orderBy('code')->each(function (Regency $regency) use (&$index, $provinceIndex) {
            $provinceCode = $regency->province_code;
            $provinceSlug = null;

            foreach ($provinceIndex as $slug => $code) {
                if ($code === $provinceCode) {
                    $provinceSlug = $slug;
                    break;
                }
            }

            if (! $provinceSlug) {
                return;
            }

            foreach ($this->candidateSlugsForName($regency->name) as $slug) {
                if ($slug === '') {
                    continue;
                }

                $index[$provinceSlug][$slug] ??= [];

                if (! in_array($regency->code, $index[$provinceSlug][$slug], true)) {
                    $index[$provinceSlug][$slug][] = $regency->code;
                }
            }
        });

        return $index;
    }

    protected function buildDistrictIndex(): array
    {
        $index = [];

        District::query()->orderBy('regency_code')->orderBy('code')->each(function (District $district) use (&$index) {
            foreach ($this->candidateSlugsForName($district->name) as $slug) {
                if ($slug === '') {
                    continue;
                }

                $index[$district->regency_code][$slug] ??= [];

                if (! in_array($district->code, $index[$district->regency_code][$slug], true)) {
                    $index[$district->regency_code][$slug][] = $district->code;
                }
            }
        });

        return $index;
    }

    protected function matchRegencyCode(string $provinceSlug, string $citySlug, string $cityNameSlug, array $regencyIndex): ?string
    {
        $slugs = array_filter([$citySlug, $cityNameSlug]);

        foreach ($slugs as $slug) {
            if ($provinceSlug !== '' && isset($regencyIndex[$provinceSlug][$slug])) {
                $codes = $regencyIndex[$provinceSlug][$slug];
                if (count($codes) === 1) {
                    return $codes[0];
                }
            }
        }

        foreach ($regencyIndex as $codesBySlug) {
            foreach ($slugs as $slug) {
                if (isset($codesBySlug[$slug]) && count($codesBySlug[$slug]) === 1) {
                    return $codesBySlug[$slug][0];
                }
            }
        }

        return null;
    }

    protected function matchDistrictCode(?string $regencyCode, string $subdistrictSlug, string $subdistrictNameSlug, array $districtIndex): ?string
    {
        $slugs = array_filter([$subdistrictSlug, $subdistrictNameSlug]);

        if ($regencyCode && isset($districtIndex[$regencyCode])) {
            foreach ($slugs as $slug) {
                if (isset($districtIndex[$regencyCode][$slug])) {
                    $codes = $districtIndex[$regencyCode][$slug];
                    if (count($codes) === 1) {
                        return $codes[0];
                    }
                }
            }
        }

        foreach ($districtIndex as $codesBySlug) {
            foreach ($slugs as $slug) {
                if (isset($codesBySlug[$slug]) && count($codesBySlug[$slug]) === 1) {
                    return $codesBySlug[$slug][0];
                }
            }
        }

        return null;
    }

    protected function candidateSlugsForName(string $value): array
    {
        $value = Str::lower($value);

        $candidates = [
            $this->normalizeName($value),
            $this->normalizeName('kabupaten '.$value),
            $this->normalizeName('kab. '.$value),
            $this->normalizeName('kota '.$value),
            $this->normalizeName('adm. '.$value),
            $this->normalizeName('administrasi '.$value),
            $this->normalizeName('kecamatan '.$value),
        ];

        return array_values(array_unique(array_filter($candidates)));
    }

    protected function normalizeName(string $value): string
    {
        $value = Str::lower($value);
        $value = str_replace([
            'kab.',
            'kabupaten',
            'kota',
            'adm.',
            'administrasi',
            'kecamatan',
        ], '', $value);

        $normalized = preg_replace('/[^a-z0-9]/', '', $value);

        return is_string($normalized) ? $normalized : '';
    }

    protected function normalizeProvinceName(string $value): string
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

    protected function baseUrl(array $config): string
    {
        return match ($config['account_type'] ?? 'starter') {
            'basic' => 'https://api.rajaongkir.com/basic',
            'pro' => 'https://pro.rajaongkir.com/api',
            default => 'https://api.rajaongkir.com/starter',
        };
    }
}
