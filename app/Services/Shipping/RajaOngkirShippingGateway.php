<?php

namespace App\Services\Shipping;

use App\Services\Shipping\Exceptions\ShippingException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RajaOngkirShippingGateway implements ShippingGateway
{
    public function key(): string
    {
        return 'rajaongkir';
    }

    public function label(): string
    {
        return 'RajaOngkir';
    }

    public function description(): string
    {
        return 'Integrasi RajaOngkir untuk pengecekan ongkir, pemesanan, dan pelacakan pengiriman.';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function configFields(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'type' => 'text',
                'rules' => 'required|string',
            ],
            [
                'key' => 'account_type',
                'label' => 'Jenis Akun',
                'type' => 'select',
                'default' => 'starter',
                'options' => [
                    ['value' => 'starter', 'label' => 'Starter'],
                    ['value' => 'basic', 'label' => 'Basic'],
                    ['value' => 'pro', 'label' => 'Pro'],
                ],
                'rules' => 'required|string|in:starter,basic,pro',
            ],
            [
                'key' => 'origin_type',
                'label' => 'Tipe Origin',
                'type' => 'select',
                'default' => 'city',
                'options' => [
                    ['value' => 'city', 'label' => 'Kota/Kabupaten'],
                    ['value' => 'subdistrict', 'label' => 'Kecamatan (Pro)'],
                ],
                'rules' => 'required|string|in:city,subdistrict',
            ],
            [
                'key' => 'origin_id',
                'label' => 'ID Origin RajaOngkir',
                'type' => 'text',
                'rules' => 'required|string',
            ],
            [
                'key' => 'couriers',
                'label' => 'Kurir yang Diaktifkan',
                'type' => 'multiselect',
                'multiple' => true,
                'default' => ['jne', 'tiki', 'pos'],
                'options' => $this->courierOptions(),
                'rules' => 'required|array|min:1',
            ],
            [
                'key' => 'default_courier',
                'label' => 'Kurir Default Pelacakan',
                'type' => 'text',
                'rules' => 'nullable|string',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function checkRates(array $payload): array
    {
        $config = $payload['config'] ?? [];
        $destination = $payload['destination'] ?? [];
        $couriers = Arr::wrap($payload['couriers'] ?? Arr::get($config, 'couriers', []));
        $weight = max(1, (int) ($payload['weight'] ?? 0));

        if (empty($config['api_key'])) {
            throw (new ShippingException('API key RajaOngkir belum dikonfigurasi.'))->withContext(['config' => $config]);
        }

        if ($weight <= 0) {
            throw new ShippingException('Berat paket tidak valid.');
        }

        if (empty($couriers)) {
            throw new ShippingException('Tidak ada kurir yang diaktifkan.');
        }

        $destinationCityId = $payload['destination_city_id'] ?? null;
        if (! $destinationCityId) {
            $provinceName = (string) ($destination['province'] ?? '');
            $cityName = (string) ($destination['regency'] ?? '');
            $destinationCityId = $this->resolveCityId($provinceName, $cityName, $config);
        }

        if (! $destinationCityId) {
            throw (new ShippingException('Tidak dapat menentukan kota tujuan.'))->withContext([
                'destination' => $destination,
            ]);
        }

        $response = Http::withHeaders([
            'key' => $config['api_key'],
        ])->asForm()->post($this->baseUrl($config).'/cost', [
            'origin' => $config['origin_id'],
            'originType' => $config['origin_type'] ?? 'city',
            'destination' => $destinationCityId,
            'destinationType' => 'city',
            'weight' => $weight,
            'courier' => implode(':', array_map('strtolower', $couriers)),
        ]);

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description')
                ?: $response->reason();
            throw (new ShippingException($message ?: 'Gagal mengambil ongkir dari RajaOngkir.'))
                ->withContext(['payload' => $payload, 'response' => $response->json()]);
        }

        $results = Arr::get($response->json(), 'rajaongkir.results', []);

        $rates = [];
        foreach ($results as $result) {
            $courierCode = strtolower((string) Arr::get($result, 'code'));
            $courierName = (string) Arr::get($result, 'name');
            foreach (Arr::get($result, 'costs', []) as $service) {
                $serviceCode = (string) Arr::get($service, 'service');
                $description = (string) Arr::get($service, 'description');
                $costData = Arr::first(Arr::get($service, 'cost', []), fn ($cost) => true) ?? [];
                $costValue = (int) Arr::get($costData, 'value', 0);
                $etd = (string) Arr::get($costData, 'etd', '');

                $rates[] = [
                    'courier' => $courierCode,
                    'courier_name' => $courierName,
                    'service' => $serviceCode,
                    'description' => $description,
                    'etd' => $etd,
                    'cost' => $costValue,
                    'currency' => 'IDR',
                ];
            }
        }

        return [
            'destination' => $destination,
            'origin' => [
                'id' => $config['origin_id'],
                'type' => $config['origin_type'] ?? 'city',
            ],
            'weight' => $weight,
            'rates' => $rates,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array
    {
        $rate = $payload['rate'] ?? [];
        $config = $payload['config'] ?? [];

        if (empty($rate['courier']) || empty($rate['service'])) {
            throw new ShippingException('Pilihan kurir tidak valid.');
        }

        return [
            'remote_id' => 'rajaongkir-'.Str::uuid()->toString(),
            'courier' => strtolower((string) $rate['courier']),
            'service' => (string) ($rate['service'] ?? ''),
            'cost' => (int) ($rate['cost'] ?? 0),
            'etd' => (string) ($rate['etd'] ?? ''),
            'description' => (string) ($rate['description'] ?? ''),
            'currency' => (string) ($rate['currency'] ?? 'IDR'),
            'config' => $config,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function track(string $trackingNumber, array $context = []): array
    {
        $config = $context['config'] ?? [];
        $courier = strtolower((string) ($context['courier'] ?? Arr::get($config, 'default_courier')));

        if (empty($config['api_key'])) {
            throw new ShippingException('API key RajaOngkir belum dikonfigurasi.');
        }

        if (! $courier) {
            throw new ShippingException('Kurir untuk pelacakan belum ditentukan.');
        }

        $response = Http::withHeaders([
            'key' => $config['api_key'],
        ])->asForm()->post($this->baseUrl($config).'/waybill', [
            'waybill' => $trackingNumber,
            'courier' => $courier,
        ]);

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description')
                ?: $response->reason();
            throw (new ShippingException($message ?: 'Gagal mengambil status pelacakan.'))
                ->withContext(['tracking_number' => $trackingNumber, 'response' => $response->json()]);
        }

        $result = Arr::get($response->json(), 'rajaongkir.result', []);

        return [
            'summary' => Arr::get($result, 'summary', []),
            'details' => Arr::get($result, 'details', []),
            'delivery_status' => Arr::get($result, 'delivery_status', []),
            'manifest' => Arr::get($result, 'manifest', []),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function cancel(string $remoteId, array $context = []): array
    {
        return [
            'remote_id' => $remoteId,
            'status' => 'cancelled',
            'message' => 'Pembatalan dicatat secara internal. RajaOngkir tidak menyediakan API pembatalan.',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function courierOptions(): array
    {
        return [
            ['value' => 'jne', 'label' => 'JNE'],
            ['value' => 'tiki', 'label' => 'TIKI'],
            ['value' => 'pos', 'label' => 'POS Indonesia'],
            ['value' => 'sicepat', 'label' => 'SiCepat'],
            ['value' => 'jnt', 'label' => 'J&T Express'],
            ['value' => 'lion', 'label' => 'Lion Parcel'],
            ['value' => 'ninja', 'label' => 'Ninja Xpress'],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function baseUrl(array $config): string
    {
        $type = $config['account_type'] ?? 'starter';

        return match ($type) {
            'basic' => 'https://api.rajaongkir.com/basic',
            'pro' => 'https://pro.rajaongkir.com/api',
            default => 'https://api.rajaongkir.com/starter',
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function resolveCityId(string $provinceName, string $cityName, array $config): ?string
    {
        if ($provinceName === '' || $cityName === '') {
            return null;
        }

        $cities = $this->getCities($config);
        $provinceSlug = $this->normalizeName($provinceName);
        $citySlug = $this->normalizeName($cityName);

        foreach ($cities as $city) {
            if ($this->normalizeName($city['province']) !== $provinceSlug) {
                continue;
            }

            $candidateSlug = $this->normalizeName(trim($city['type'].' '.$city['city_name']));
            $cityNameSlug = $this->normalizeName($city['city_name']);

            if ($candidateSlug === $citySlug || $cityNameSlug === $citySlug) {
                return (string) $city['city_id'];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array<string, mixed>>
     */
    protected function getCities(array $config): array
    {
        $cacheKey = 'shipping.rajaongkir.cities.'.($config['account_type'] ?? 'starter');

        return Cache::remember($cacheKey, now()->addDay(), function () use ($config) {
            if (empty($config['api_key'])) {
                return [];
            }

            $response = Http::withHeaders([
                'key' => $config['api_key'],
            ])->get($this->baseUrl($config).'/city');

            if ($response->failed()) {
                return [];
            }

            return Arr::get($response->json(), 'rajaongkir.results', []);
        });
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

        // Remove non-alphanumeric characters to make the comparison resilient to
        // punctuation differences between local datasets and RajaOngkir.
        return preg_replace('/[^a-z0-9]/', '', $value);
    }
}
