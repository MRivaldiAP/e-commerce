<?php

namespace App\Services\Shipping;

use App\Models\RajaOngkirCity;
use App\Models\RajaOngkirSubdistrict;
use App\Services\Shipping\Exceptions\ShippingException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RajaOngkirShippingGateway implements ShippingGateway
{
    public function __construct(
        protected RajaOngkirLocationSyncService $locationSync,
    ) {
    }

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
        $originField = [
            'key' => 'origin_id',
            'label' => 'ID Origin RajaOngkir',
            'rules' => 'required|string',
        ];

        $originOptions = $this->originOptions();

        if (! empty($originOptions)) {
            $originField['type'] = 'select';
            $originField['options'] = $originOptions;
            $originField['help'] = 'Pilih ID origin berdasarkan hasil sinkronisasi data RajaOngkir.';
        } else {
            $originField['type'] = 'text';
            $originField['help'] = 'Masukkan ID origin sesuai daftar RajaOngkir atau sinkronkan data terlebih dahulu.';
        }

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
            $originField,
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
     * @param  array<string, mixed>  $config
     */
    public function afterConfigUpdated(array $config): void
    {
        $this->locationSync->sync($config);
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
            $destinationCityId = $this->resolveDestinationCityId($destination, $config);
        }

        $destinationSubdistrictId = null;
        if (($config['account_type'] ?? 'starter') === 'pro') {
            $destinationSubdistrictId = $this->resolveDestinationSubdistrictId($destination, $config);
        }

        $resolvedDestinationId = $destinationSubdistrictId ?: $destinationCityId;
        $resolvedDestinationType = $destinationSubdistrictId ? 'subdistrict' : 'city';

        if (! $resolvedDestinationId) {
            throw (new ShippingException('Tidak dapat menentukan kota tujuan.'))->withContext([
                'destination' => $destination,
            ]);
        }

        $response = Http::withHeaders([
            'key' => $config['api_key'],
        ])->asForm()->post($this->baseUrl($config).'/cost', [
            'origin' => $config['origin_id'],
            'originType' => $config['origin_type'] ?? 'city',
            'destination' => $resolvedDestinationId,
            'destinationType' => $resolvedDestinationType,
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
                'city_id' => $destinationCityId,
                'subdistrict_id' => $destinationSubdistrictId,
            ]),
            'origin' => [
                'id' => $config['origin_id'],
                'type' => $config['origin_type'] ?? 'city',
            ],
            'weight' => $weight,
            'rates' => $rates,
            'destination_city_id' => $destinationCityId,
            'destination_subdistrict_id' => $destinationSubdistrictId,
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
    protected function originOptions(): array
    {
        $options = [];

        RajaOngkirCity::query()
            ->orderBy('province')
            ->orderBy('city_name')
            ->each(function (RajaOngkirCity $city) use (&$options) {
                $label = sprintf('[%s] %s, %s (ID %s)',
                    Str::upper($city->type),
                    $city->city_name,
                    $city->province,
                    $city->city_id,
                );

                $options[] = [
                    'value' => (string) $city->city_id,
                    'label' => $label,
                ];
            });

        RajaOngkirSubdistrict::query()
            ->orderBy('province')
            ->orderBy('city')
            ->orderBy('subdistrict_name')
            ->each(function (RajaOngkirSubdistrict $subdistrict) use (&$options) {
                $label = sprintf('[%s] %s, %s (ID %s)',
                    Str::upper($subdistrict->type ?: 'Kecamatan'),
                    $subdistrict->subdistrict_name,
                    $subdistrict->city,
                    $subdistrict->subdistrict_id,
                );

                $options[] = [
                    'value' => (string) $subdistrict->subdistrict_id,
                    'label' => $label,
                ];
            });

        return $options;
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
     * @param  array<string, mixed>  $destination
     * @param  array<string, mixed>  $config
     */
    protected function resolveDestinationCityId(array $destination, array $config): ?string
    {
        $regencyCode = (string) ($destination['regency_code'] ?? '');

        if ($regencyCode !== '') {
            $match = RajaOngkirCity::query()
                ->where('nusa_regency_code', $regencyCode)
                ->first();

            if ($match) {
                return (string) $match->city_id;
            }
        }

        $provinceName = (string) ($destination['province'] ?? '');
        $cityName = (string) ($destination['regency'] ?? '');

        if ($provinceName === '' || $cityName === '') {
            return null;
        }

        return $this->resolveCityId($provinceName, $cityName, $config);
    }

    /**
     * @param  array<string, mixed>  $destination
     * @param  array<string, mixed>  $config
     */
    protected function resolveDestinationSubdistrictId(array $destination, array $config): ?string
    {
        $districtCode = (string) ($destination['district_code'] ?? '');

        if ($districtCode !== '') {
            $match = RajaOngkirSubdistrict::query()
                ->where('nusa_district_code', $districtCode)
                ->first();

            if ($match) {
                return (string) $match->subdistrict_id;
            }
        }

        $provinceName = (string) ($destination['province'] ?? '');
        $cityName = (string) ($destination['regency'] ?? '');
        $districtName = (string) ($destination['district'] ?? '');

        if ($cityName === '' || $districtName === '') {
            return null;
        }

        $cityId = $this->resolveCityId($provinceName, $cityName, $config);

        if (! $cityId) {
            return null;
        }

        $subdistricts = $this->getSubdistricts($config, $cityId);
        $districtSlug = $this->normalizeName($districtName);

        foreach ($subdistricts as $subdistrict) {
            if ((string) Arr::get($subdistrict, 'city_id') !== $cityId) {
                continue;
            }

            if ($this->subdistrictMatchesSlug($subdistrict, $districtSlug)) {
                return (string) Arr::get($subdistrict, 'subdistrict_id');
            }
        }

        return null;
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

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array<string, mixed>>
     */
    protected function getCities(array $config): array
    {
        $cities = RajaOngkirCity::query()
            ->orderBy('province')
            ->orderBy('city_name')
            ->get()
            ->map(fn (RajaOngkirCity $city) => $city->toArray())
            ->all();

        if (empty($cities) && ! empty($config['api_key'])) {
            $cities = $this->locationSync->syncCities($config)
                ->map(fn (RajaOngkirCity $city) => $city->toArray())
                ->all();
        }

        return array_map(fn ($city) => (array) $city, $cities);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array<string, mixed>>
     */
    protected function getSubdistricts(array $config, ?string $cityId = null): array
    {
        $query = RajaOngkirSubdistrict::query()
            ->orderBy('province')
            ->orderBy('city')
            ->orderBy('subdistrict_name');

        if ($cityId) {
            $query->where('city_id', $cityId);
        }

        $subdistricts = $query->get()
            ->map(fn (RajaOngkirSubdistrict $subdistrict) => $subdistrict->toArray())
            ->all();

        if (empty($subdistricts) && ($config['account_type'] ?? 'starter') === 'pro' && ! empty($config['api_key'])) {
            $this->locationSync->syncSubdistricts($config);

            $query = RajaOngkirSubdistrict::query()
                ->orderBy('province')
                ->orderBy('city')
                ->orderBy('subdistrict_name');

            if ($cityId) {
                $query->where('city_id', $cityId);
            }

            $subdistricts = $query->get()
                ->map(fn (RajaOngkirSubdistrict $subdistrict) => $subdistrict->toArray())
                ->all();
        }

        return array_map(fn ($subdistrict) => (array) $subdistrict, $subdistricts);
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

        $candidateSlug = $this->normalizeName(trim(sprintf('%s %s', Arr::get($subdistrict, 'type', ''), Arr::get($subdistrict, 'subdistrict_name', ''))));
        $nameSlug = $this->normalizeName((string) Arr::get($subdistrict, 'subdistrict_name', ''));

        return $candidateSlug === $slug || $nameSlug === $slug;
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
