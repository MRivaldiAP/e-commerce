<?php

namespace App\Services\Shipping;

use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirSubdistrict;
use App\Services\Shipping\Exceptions\ShippingException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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
                'type' => 'select',
                'options' => $this->originOptions(),
                'rules' => 'required|string',
                'help' => 'Pilih lokasi origin yang telah disinkronkan dari RajaOngkir.',
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
     * @return array<int, array<string, mixed>>
     */
    protected function originOptions(): array
    {
        if (! Schema::hasTable('rajaongkir_cities')) {
            return [];
        }

        $options = [];

        $cities = RajaOngkirCity::query()
            ->orderBy('province_name')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        foreach ($cities as $city) {
            $labelParts = array_filter([
                trim((string) $city->type),
                trim((string) $city->name),
            ]);
            $label = trim(implode(' ', $labelParts));
            if ($label === '') {
                $label = (string) $city->name;
            }

            $options[] = [
                'value' => (string) $city->id,
                'label' => trim($label.' ('.$city->province_name.')'),
                'data' => [
                    'origin-type' => 'city',
                ],
            ];
        }

        if (Schema::hasTable('rajaongkir_subdistricts')) {
            $subdistricts = RajaOngkirSubdistrict::query()
                ->orderBy('province_name')
                ->orderBy('city_name')
                ->orderBy('name')
                ->get();

            foreach ($subdistricts as $subdistrict) {
                $labelParts = [trim((string) $subdistrict->name)];
                if ($subdistrict->city_name !== '') {
                    $labelParts[] = trim((string) $subdistrict->city_name);
                }

                $label = trim(implode(', ', array_filter($labelParts)).' ('.$subdistrict->province_name.')');

                $options[] = [
                    'value' => (string) $subdistrict->id,
                    'label' => $label,
                    'data' => [
                        'origin-type' => 'subdistrict',
                    ],
                ];
            }
        }

        return $options;
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

        $destinationId = $payload['destination_id']
            ?? $payload['destination_city_id']
            ?? null;
        $destinationType = $payload['destination_type'] ?? null;

        if (! $destinationId) {
            $provinceName = (string) ($destination['province'] ?? '');
            $cityName = (string) ($destination['regency'] ?? '');
            $destinationId = $this->resolveCityId($provinceName, $cityName, $config);

            if (($config['account_type'] ?? 'starter') === 'pro') {
                $districtName = (string) ($destination['district'] ?? '');
                $subdistrictId = $this->resolveSubdistrictId($provinceName, $cityName, $districtName, $config, $destinationId);

                if ($subdistrictId) {
                    $destinationId = $subdistrictId;
                    $destinationType = 'subdistrict';
                }
            }
        }

        if (! $destinationId) {
            throw (new ShippingException('Tidak dapat menentukan kota tujuan.'))->withContext([
                'destination' => $destination,
            ]);
        }

        if (! $destinationType) {
            $destinationType = 'city';
        }

        $response = Http::withHeaders([
            'key' => $config['api_key'],
        ])->asForm()->post($this->baseUrl($config).'/cost', [
            'origin' => $config['origin_id'],
            'originType' => $config['origin_type'] ?? 'city',
            'destination' => $destinationId,
            'destinationType' => $destinationType,
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
            'destination' => array_merge($destination, [
                'rajaongkir_id' => $destinationId,
                'rajaongkir_type' => $destinationType,
                'rajaongkir_city_id' => $destinationType === 'city' ? $destinationId : null,
                'rajaongkir_subdistrict_id' => $destinationType === 'subdistrict' ? $destinationId : null,
            ]),
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
        $provinceSlug = $this->normalizeProvinceName($provinceName);
        $citySlug = $this->normalizeName($cityName);

        $fallbackMatches = [];

        foreach ($cities as $city) {
            $cityProvinceSlug = $this->normalizeProvinceName((string) Arr::get($city, 'province', ''));

            if ($provinceSlug !== '' && $provinceSlug === $cityProvinceSlug && $this->cityMatchesSlug($city, $citySlug)) {
                return (string) Arr::get($city, 'city_id');
            }

            if ($provinceSlug === $cityProvinceSlug) {
                $fallbackMatches[] = $city;
            }
        }

        // If no city was found using province matching, attempt to locate a
        // single match using the city name alone. This mirrors the behaviour of
        // the RajaOngkir dashboard when users search for a destination.
        if ($provinceSlug === '' || empty($fallbackMatches)) {
            $matches = [];

            foreach ($cities as $city) {
                if ($this->cityMatchesSlug($city, $citySlug)) {
                    $matches[(string) Arr::get($city, 'city_id')] = $city;
                }
            }

            if (count($matches) === 1) {
                return (string) array_key_first($matches);
            }
        }

        foreach ($fallbackMatches as $city) {
            if ($this->cityMatchesSlug($city, $citySlug)) {
                return (string) Arr::get($city, 'city_id');
            }
        }

        return null;
    }

    protected function resolveSubdistrictId(string $provinceName, string $cityName, string $subdistrictName, array $config, ?string $cityId = null): ?string
    {
        if ($provinceName === '' || $cityName === '' || $subdistrictName === '') {
            return null;
        }

        $subdistricts = $this->getSubdistricts($config, $cityId);
        if (empty($subdistricts)) {
            return null;
        }

        $provinceSlug = $this->normalizeProvinceName($provinceName);
        $citySlug = $this->normalizeName($cityName);
        $subdistrictSlug = $this->normalizeName($subdistrictName);

        $fallbackMatches = [];

        foreach ($subdistricts as $subdistrict) {
            $provinceMatchSlug = $this->normalizeProvinceName((string) Arr::get($subdistrict, 'province', ''));
            $cityMatchSlug = $this->normalizeName((string) Arr::get($subdistrict, 'city', ''));

            if ($provinceSlug !== '' && $provinceSlug !== $provinceMatchSlug) {
                continue;
            }

            if ($citySlug !== '' && $citySlug !== $cityMatchSlug) {
                continue;
            }

            if ($this->subdistrictMatchesSlug($subdistrict, $subdistrictSlug)) {
                return (string) Arr::get($subdistrict, 'subdistrict_id');
            }

            $fallbackMatches[] = $subdistrict;
        }

        foreach ($fallbackMatches as $subdistrict) {
            if ($this->subdistrictMatchesSlug($subdistrict, $subdistrictSlug)) {
                return (string) Arr::get($subdistrict, 'subdistrict_id');
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
        if (Schema::hasTable('rajaongkir_cities')) {
            $cities = RajaOngkirCity::query()
                ->orderBy('province_name')
                ->orderBy('name')
                ->get();

            if ($cities->isNotEmpty()) {
                return $cities->map(function (RajaOngkirCity $city) {
                    return [
                        'city_id' => (string) $city->id,
                        'city_name' => (string) $city->name,
                        'province' => (string) $city->province_name,
                        'province_id' => (string) $city->province_id,
                        'type' => (string) $city->type,
                    ];
                })->all();
            }
        }

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

    protected function getSubdistricts(array $config, ?string $cityId = null): array
    {
        if (! Schema::hasTable('rajaongkir_subdistricts')) {
            return [];
        }

        $query = RajaOngkirSubdistrict::query();

        if ($cityId) {
            $query->where('city_id', $cityId);
        }

        $subdistricts = $query->get();

        if ($subdistricts->isEmpty()) {
            return [];
        }

        return $subdistricts->map(function (RajaOngkirSubdistrict $subdistrict) {
            return [
                'subdistrict_id' => (string) $subdistrict->id,
                'subdistrict_name' => (string) $subdistrict->name,
                'city' => (string) $subdistrict->city_name,
                'city_id' => (string) $subdistrict->city_id,
                'province' => (string) $subdistrict->province_name,
                'province_id' => (string) $subdistrict->province_id,
                'type' => (string) $subdistrict->type,
            ];
        })->all();
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
            'kec.',
            'kecamatan',
            'kel.',
            'kelurahan',
        ], '', $value);

        // Remove non-alphanumeric characters to make the comparison resilient to
        // punctuation differences between local datasets and RajaOngkir.

        $normalized = preg_replace('/[^a-z0-9]/', '', $value);

        return is_string($normalized) ? $normalized : '';
    }

    /**
     * @param  array<string, mixed>  $city
     */
    protected function cityMatchesSlug(array $city, string $slug): bool
    {
        if ($slug === '') {
            return false;
        }

        $candidateSlug = $this->normalizeName(trim(sprintf('%s %s', Arr::get($city, 'type', ''), Arr::get($city, 'city_name', ''))));
        $cityNameSlug = $this->normalizeName((string) Arr::get($city, 'city_name', ''));

        return $candidateSlug === $slug || $cityNameSlug === $slug;
    }

    /**
     * @param  array<string, mixed>  $subdistrict
     */
    protected function subdistrictMatchesSlug(array $subdistrict, string $slug): bool
    {
        if ($slug === '') {
            return false;
        }

        $subdistrictSlug = $this->normalizeName((string) Arr::get($subdistrict, 'subdistrict_name', ''));

        return $subdistrictSlug === $slug;
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

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        return $normalized;
    }
}
