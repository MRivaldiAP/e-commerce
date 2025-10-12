<?php

namespace App\Services\Shipping;

use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirProvince;
use App\Models\RajaOngkirSubdistrict;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirLocationImporter
{
    public function sync(string $apiKey, string $accountType = 'starter'): void
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            return;
        }

        $accountType = strtolower($accountType ?: 'starter');
        $baseUrl = $this->baseUrl($accountType);

        $provinces = $this->request($apiKey, $baseUrl.'/province');
        if (! empty($provinces)) {
            $this->storeProvinces($provinces);
        }

        $cities = $this->request($apiKey, $baseUrl.'/city');
        if (! empty($cities)) {
            $this->storeCities($cities);
        }

        if ($accountType === 'pro') {
            $this->syncSubdistricts($apiKey, $baseUrl, $cities);
        } else {
            $this->clearSubdistricts();
        }
    }

    /**
     * @param  array<int, mixed>  $provinces
     */
    protected function storeProvinces(array $provinces): void
    {
        $now = now();
        $rows = Collection::make($provinces)
            ->map(function ($province) use ($now) {
                $id = (string) Arr::get($province, 'province_id', '');
                $name = (string) Arr::get($province, 'province', '');

                if ($id === '' || $name === '') {
                    return null;
                }

                return [
                    'id' => $id,
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (empty($rows)) {
            return;
        }

        RajaOngkirProvince::upsert($rows, ['id'], ['name', 'updated_at']);
        RajaOngkirProvince::query()->whereNotIn('id', array_column($rows, 'id'))->delete();
    }

    /**
     * @param  array<int, mixed>  $cities
     */
    protected function storeCities(array $cities): void
    {
        $now = now();
        $rows = Collection::make($cities)
            ->map(function ($city) use ($now) {
                $id = (string) Arr::get($city, 'city_id', '');
                $name = (string) Arr::get($city, 'city_name', '');
                $provinceId = (string) Arr::get($city, 'province_id', '');
                $provinceName = (string) Arr::get($city, 'province', '');
                $type = (string) Arr::get($city, 'type', '');
                $postalCode = (string) Arr::get($city, 'postal_code', '');

                if ($id === '' || $name === '' || $provinceId === '') {
                    return null;
                }

                return [
                    'id' => $id,
                    'name' => $name,
                    'province_id' => $provinceId,
                    'province_name' => $provinceName,
                    'type' => $type,
                    'postal_code' => $postalCode ?: null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (empty($rows)) {
            return;
        }

        RajaOngkirCity::upsert($rows, ['id'], ['name', 'province_id', 'province_name', 'type', 'postal_code', 'updated_at']);
        RajaOngkirCity::query()->whereNotIn('id', array_column($rows, 'id'))->delete();
    }

    /**
     * @param  array<int, mixed>  $cities
     */
    protected function syncSubdistricts(string $apiKey, string $baseUrl, array $cities): void
    {
        if (empty($cities)) {
            $cities = RajaOngkirCity::query()
                ->get(['id as city_id', 'province_id', 'province_name', 'name as city_name', 'type'])
                ->map(fn ($city) => $city->toArray())
                ->all();
        }

        if (empty($cities)) {
            return;
        }

        $allSubdistricts = [];

        foreach ($cities as $city) {
            $cityId = (string) Arr::get($city, 'city_id', '');
            if ($cityId === '') {
                continue;
            }

            $results = $this->request($apiKey, $baseUrl.'/subdistrict', ['city' => $cityId]);
            if (empty($results)) {
                continue;
            }

            foreach ($results as $subdistrict) {
                $id = (string) Arr::get($subdistrict, 'subdistrict_id', '');
                $name = (string) Arr::get($subdistrict, 'subdistrict_name', '');
                $provinceId = (string) Arr::get($subdistrict, 'province_id', Arr::get($city, 'province_id', ''));
                $provinceName = (string) Arr::get($subdistrict, 'province', Arr::get($city, 'province_name', ''));
                $cityName = (string) Arr::get($subdistrict, 'city', Arr::get($city, 'city_name', ''));
                $type = (string) Arr::get($subdistrict, 'type', Arr::get($city, 'type', ''));

                if ($id === '' || $name === '') {
                    continue;
                }

                $allSubdistricts[$id] = [
                    'id' => $id,
                    'name' => $name,
                    'city_id' => $cityId,
                    'city_name' => $cityName,
                    'province_id' => $provinceId,
                    'province_name' => $provinceName,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($allSubdistricts)) {
            return;
        }

        $rows = array_values($allSubdistricts);

        RajaOngkirSubdistrict::upsert($rows, ['id'], ['name', 'city_id', 'city_name', 'province_id', 'province_name', 'type', 'updated_at']);
        RajaOngkirSubdistrict::query()->whereNotIn('id', array_keys($allSubdistricts))->delete();
    }

    protected function clearSubdistricts(): void
    {
        RajaOngkirSubdistrict::query()->delete();
    }

    /**
     * @return array<int, mixed>
     */
    protected function request(string $apiKey, string $url, array $query = []): array
    {
        $response = Http::withHeaders([
            'key' => $apiKey,
        ])->get($url, $query);

        if ($response->failed()) {
            Log::warning('Gagal mengambil data lokasi RajaOngkir', [
                'url' => $url,
                'query' => $query,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [];
        }

        $results = Arr::get($response->json(), 'rajaongkir.results', []);

        if (is_array($results)) {
            return $results;
        }

        if (is_object($results)) {
            return (array) $results;
        }

        return [];
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
