<?php

namespace App\Services\Shipping;

use App\Models\RajaOngkirLocation;
use App\Services\Shipping\Exceptions\ShippingException;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RajaOngkirLocationService
{
    protected ?Collection $provinceLookup = null;

    protected ?Collection $regencyLookup = null;

    protected ?Collection $districtLookup = null;

    public function __construct(protected DatabaseManager $db)
    {
    }

    public function sync(string $apiKey, string $accountType, bool $force = false): void
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            throw new ShippingException('API key RajaOngkir wajib diisi untuk sinkronisasi lokasi.');
        }

        $accountType = $accountType ?: 'starter';

        $shouldSyncCities = $force || ! RajaOngkirLocation::where('level', 'city')->exists();
        $shouldSyncSubdistricts = $accountType === 'pro'
            && ($force || ! RajaOngkirLocation::where('level', 'subdistrict')->exists());

        if (! $shouldSyncCities && ! $shouldSyncSubdistricts) {
            return;
        }

        $cities = $this->fetchCities($apiKey, $accountType);

        $this->db->transaction(function () use ($cities, $apiKey, $accountType, $shouldSyncSubdistricts) {
            $cityIds = [];

            foreach ($cities as $city) {
                $cityId = (string) Arr::get($city, 'city_id');
                if ($cityId === '') {
                    continue;
                }

                $cityIds[] = $cityId;
                $regency = $this->matchRegency($city);

                RajaOngkirLocation::updateOrCreate(
                    ['level' => 'city', 'remote_id' => $cityId],
                    [
                        'name' => (string) Arr::get($city, 'city_name', ''),
                        'type' => (string) Arr::get($city, 'type', ''),
                        'province' => (string) Arr::get($city, 'province', ''),
                        'province_code' => $regency['province_code'] ?? null,
                        'city' => (string) Arr::get($city, 'city_name', ''),
                        'city_code' => $regency['code'] ?? null,
                        'nusa_regency_code' => $regency['code'] ?? null,
                        'meta' => $city,
                    ]
                );
            }

            if (! empty($cityIds)) {
                RajaOngkirLocation::where('level', 'city')->whereNotIn('remote_id', $cityIds)->delete();
            }

            if (! $shouldSyncSubdistricts) {
                return;
            }

            $subdistrictIds = [];

            foreach ($cities as $city) {
                $cityId = (string) Arr::get($city, 'city_id');
                if ($cityId === '') {
                    continue;
                }

                $subdistricts = $this->fetchSubdistricts($apiKey, $accountType, $cityId);

                foreach ($subdistricts as $subdistrict) {
                    $subdistrictId = (string) Arr::get($subdistrict, 'subdistrict_id');
                    if ($subdistrictId === '') {
                        continue;
                    }

                    $subdistrictIds[] = $subdistrictId;
                    $district = $this->matchDistrict($subdistrict);

                    RajaOngkirLocation::updateOrCreate(
                        ['level' => 'subdistrict', 'remote_id' => $subdistrictId],
                        [
                            'name' => (string) Arr::get($subdistrict, 'subdistrict_name', ''),
                            'type' => (string) Arr::get($subdistrict, 'type', ''),
                            'province' => (string) Arr::get($subdistrict, 'province', ''),
                            'province_code' => $district['province_code'] ?? null,
                            'city' => (string) Arr::get($subdistrict, 'city', ''),
                            'city_code' => $district['regency_code'] ?? null,
                            'nusa_regency_code' => $district['regency_code'] ?? null,
                            'nusa_district_code' => $district['code'] ?? null,
                            'meta' => $subdistrict,
                        ]
                    );
                }
            }

            if (! empty($subdistrictIds)) {
                RajaOngkirLocation::where('level', 'subdistrict')->whereNotIn('remote_id', $subdistrictIds)->delete();
            }
        });
    }

    public function originOptions(string $accountType, string $originType): array
    {
        $query = RajaOngkirLocation::query();

        if ($accountType === 'pro' && $originType === 'subdistrict') {
            $query->where('level', 'subdistrict');
            $query->orderBy('province')->orderBy('city')->orderBy('name');
        } else {
            $query->where('level', 'city');
            $query->orderBy('province')->orderBy('name');
        }

        return $query->get()->map(fn (RajaOngkirLocation $location) => [
            'value' => $location->remote_id,
            'label' => $location->label(),
        ])->all();
    }

    public function findCityByRegencyCode(?string $regencyCode): ?RajaOngkirLocation
    {
        if (! $regencyCode) {
            return null;
        }

        return RajaOngkirLocation::where('level', 'city')
            ->where('nusa_regency_code', $regencyCode)
            ->first();
    }

    public function findSubdistrictByDistrictCode(?string $districtCode): ?RajaOngkirLocation
    {
        if (! $districtCode) {
            return null;
        }

        return RajaOngkirLocation::where('level', 'subdistrict')
            ->where('nusa_district_code', $districtCode)
            ->first();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function citiesForMatching(): array
    {
        return RajaOngkirLocation::where('level', 'city')
            ->get()
            ->map(fn (RajaOngkirLocation $location) => [
                'city_id' => $location->remote_id,
                'city_name' => $location->name,
                'type' => $location->type,
                'province' => $location->province,
            ])->all();
    }

    protected function fetchCities(string $apiKey, string $accountType): array
    {
        $response = Http::withHeaders([
            'key' => $apiKey,
        ])->get($this->baseUrl($accountType).'/city');

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description') ?: $response->reason();
            throw (new ShippingException($message ?: 'Gagal mengambil daftar kota dari RajaOngkir.'))
                ->withContext(['response' => $response->json()]);
        }

        return Arr::get($response->json(), 'rajaongkir.results', []);
    }

    protected function fetchSubdistricts(string $apiKey, string $accountType, string $cityId): array
    {
        $response = Http::withHeaders([
            'key' => $apiKey,
        ])->get($this->baseUrl($accountType).'/subdistrict', [
            'city' => $cityId,
        ]);

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description') ?: $response->reason();
            throw (new ShippingException($message ?: 'Gagal mengambil daftar kecamatan dari RajaOngkir.'))
                ->withContext(['response' => $response->json(), 'city_id' => $cityId]);
        }

        return Arr::get($response->json(), 'rajaongkir.results', []);
    }

    protected function matchRegency(array $city): array
    {
        $provinceSlug = $this->normalizeProvinceName((string) Arr::get($city, 'province', ''));
        $citySlug = $this->normalizeName(trim(sprintf('%s %s', Arr::get($city, 'type', ''), Arr::get($city, 'city_name', ''))));
        $cityNameSlug = $this->normalizeName((string) Arr::get($city, 'city_name', ''));

        $matches = $this->regencies()
            ->filter(function (array $regency) use ($provinceSlug, $citySlug, $cityNameSlug) {
                if ($provinceSlug !== '' && $provinceSlug !== $regency['province_slug']) {
                    return false;
                }

                return $regency['slug'] === $citySlug || $regency['name_slug'] === $cityNameSlug;
            });

        if ($matches->isEmpty()) {
            return [];
        }

        if ($matches->count() === 1) {
            return $matches->first();
        }

        $preferred = $matches->first(fn (array $match) => str_contains((string) $match['code'], '.'));

        return $preferred ?? $matches->first();
    }

    protected function matchDistrict(array $subdistrict): array
    {
        $provinceSlug = $this->normalizeProvinceName((string) Arr::get($subdistrict, 'province', ''));
        $regencySlug = $this->normalizeName((string) Arr::get($subdistrict, 'city', ''));
        $districtSlug = $this->normalizeName((string) Arr::get($subdistrict, 'subdistrict_name', ''));

        $matches = $this->districts()
            ->filter(function (array $district) use ($provinceSlug, $regencySlug, $districtSlug) {
                if ($provinceSlug !== '' && $provinceSlug !== $district['province_slug']) {
                    return false;
                }

                if ($regencySlug !== '' && $regencySlug !== $district['regency_slug']) {
                    return false;
                }

                return $district['slug'] === $districtSlug || $district['name_slug'] === $districtSlug;
            });

        if ($matches->isEmpty()) {
            return [];
        }

        if ($matches->count() === 1) {
            return $matches->first();
        }

        $preferred = $matches->first(fn (array $match) => str_contains((string) $match['code'], '.'));

        return $preferred ?? $matches->first();
    }

    protected function regencies(): Collection
    {
        if ($this->regencyLookup !== null) {
            return $this->regencyLookup;
        }

        $provinces = $this->provinces();

        return $this->regencyLookup = Regency::query()
            ->get(['code', 'name', 'province_code'])
            ->map(function (Regency $regency) use ($provinces) {
                $province = $provinces->firstWhere('code', $regency->province_code);
                $provinceSlug = $province['slug'] ?? '';

                return [
                    'code' => $regency->code,
                    'name' => $regency->name,
                    'slug' => $this->normalizeName($regency->name),
                    'name_slug' => $this->normalizeName($regency->name),
                    'province_code' => $regency->province_code,
                    'province_slug' => $provinceSlug,
                ];
            });
    }

    protected function provinces(): Collection
    {
        if ($this->provinceLookup !== null) {
            return $this->provinceLookup;
        }

        return $this->provinceLookup = Province::query()
            ->get(['code', 'name'])
            ->map(fn (Province $province) => [
                'code' => $province->code,
                'name' => $province->name,
                'slug' => $this->normalizeProvinceName($province->name),
            ]);
    }

    protected function districts(): Collection
    {
        if ($this->districtLookup !== null) {
            return $this->districtLookup;
        }

        $regencies = $this->regencies();

        return $this->districtLookup = District::query()
            ->get(['code', 'name', 'regency_code'])
            ->map(function (District $district) use ($regencies) {
                $regency = $regencies->firstWhere('code', $district->regency_code);

                return [
                    'code' => $district->code,
                    'name' => $district->name,
                    'slug' => $this->normalizeName($district->name),
                    'name_slug' => $this->normalizeName($district->name),
                    'regency_code' => $district->regency_code,
                    'regency_slug' => $regency['slug'] ?? '',
                    'province_code' => $regency['province_code'] ?? null,
                    'province_slug' => $regency['province_slug'] ?? '',
                ];
            });
    }

    protected function baseUrl(string $accountType): string
    {
        return match ($accountType) {
            'basic' => 'https://api.rajaongkir.com/basic',
            'pro' => 'https://pro.rajaongkir.com/api',
            default => 'https://api.rajaongkir.com/starter',
        };
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
}
