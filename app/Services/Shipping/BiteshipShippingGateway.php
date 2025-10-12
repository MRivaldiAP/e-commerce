<?php

namespace App\Services\Shipping;

use App\Services\Shipping\Exceptions\ShippingException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BiteshipShippingGateway implements ShippingGateway
{
    public function key(): string
    {
        return 'biteship';
    }

    public function label(): string
    {
        return 'Biteship';
    }

    public function description(): string
    {
        return 'Integrasi layanan pengiriman domestik menggunakan Biteship.';
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
                'type' => 'password',
                'rules' => 'required|string',
            ],
            [
                'key' => 'origin_contact_name',
                'label' => 'Nama Kontak Origin',
                'type' => 'text',
                'rules' => 'required|string',
            ],
            [
                'key' => 'origin_contact_phone',
                'label' => 'Nomor Telepon Origin',
                'type' => 'text',
                'rules' => 'required|string',
            ],
            [
                'key' => 'origin_address',
                'label' => 'Alamat Origin',
                'type' => 'textarea',
                'rules' => 'required|string',
            ],
            [
                'key' => 'origin_postal_code',
                'label' => 'Kode Pos Origin',
                'type' => 'text',
                'rules' => 'required|string',
            ],
            [
                'key' => 'origin_area_id',
                'label' => 'Area ID Origin (Opsional)',
                'type' => 'text',
                'rules' => 'nullable|string',
                'help' => 'Gunakan area_id dari API lokasi Biteship untuk akurasi yang lebih baik.',
            ],
            [
                'key' => 'origin_latitude',
                'label' => 'Latitude Origin (Opsional)',
                'type' => 'text',
                'rules' => 'nullable|string',
            ],
            [
                'key' => 'origin_longitude',
                'label' => 'Longitude Origin (Opsional)',
                'type' => 'text',
                'rules' => 'nullable|string',
            ],
            [
                'key' => 'couriers',
                'label' => 'Kurir yang Diaktifkan',
                'type' => 'multiselect',
                'multiple' => true,
                'default' => ['jne', 'sicepat', 'jnt'],
                'options' => $this->courierOptions(),
                'rules' => 'required|array|min:1',
            ],
            [
                'key' => 'default_courier',
                'label' => 'Kurir Default Pelacakan',
                'type' => 'text',
                'rules' => 'nullable|string',
                'help' => 'Digunakan saat melacak resi jika kurir tidak ditentukan.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function courierOptions(): array
    {
        return [
            ['value' => 'jne', 'label' => 'JNE'],
            ['value' => 'pos', 'label' => 'POS Indonesia'],
            ['value' => 'jnt', 'label' => 'J&T Express'],
            ['value' => 'sicepat', 'label' => 'SiCepat'],
            ['value' => 'anteraja', 'label' => 'AnterAja'],
            ['value' => 'wahana', 'label' => 'Wahana Express'],
            ['value' => 'lion', 'label' => 'Lion Parcel'],
            ['value' => 'ninja', 'label' => 'Ninja Xpress'],
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
        $weight = max(1, (int) ($payload['weight'] ?? 0));
        $couriers = array_filter(array_map('strtolower', Arr::wrap($payload['couriers'] ?? Arr::get($config, 'couriers', []))));

        if (empty($config['api_key'])) {
            throw new ShippingException('API key Biteship belum dikonfigurasi.');
        }

        if (empty($config['origin_postal_code'])) {
            throw new ShippingException('Kode pos origin Biteship belum dikonfigurasi.');
        }

        if (empty($destination['postal_code'])) {
            throw new ShippingException('Kode pos tujuan belum ditentukan.');
        }

        if ($weight <= 0) {
            throw new ShippingException('Berat paket tidak valid.');
        }

        if (empty($couriers)) {
            throw new ShippingException('Tidak ada kurir Biteship yang diaktifkan.');
        }

        $requestPayload = $this->filterNull([
            'origin_postal_code' => $config['origin_postal_code'],
            'origin_area_id' => $config['origin_area_id'] ?? null,
            'origin_latitude' => $config['origin_latitude'] ?? null,
            'origin_longitude' => $config['origin_longitude'] ?? null,
            'destination_postal_code' => $destination['postal_code'],
            'destination_area_id' => Arr::get($payload, 'destination_codes.biteship_area_id'),
            'couriers' => implode(',', $couriers),
            'items' => [
                [
                    'name' => 'Checkout Items',
                    'value' => (int) round($payload['subtotal'] ?? 0),
                    'quantity' => 1,
                    'weight' => $weight,
                ],
            ],
        ]);

        $response = $this->http($config)->post('/rates/couriers', $requestPayload);

        if ($response->failed()) {
            throw new ShippingException($this->resolveErrorMessage($response) ?: 'Gagal mengambil ongkir dari Biteship.');
        }

        $pricing = Arr::get($response->json(), 'pricing', []);
        $rates = [];
        foreach ($pricing as $rate) {
            $price = Arr::get($rate, 'price.total', Arr::get($rate, 'price', 0));
            $etd = $this->formatEtd($rate);

            $rates[] = [
                'courier' => strtolower((string) (Arr::get($rate, 'courier_code', Arr::get($rate, 'courier', '')))),
                'courier_name' => (string) (Arr::get($rate, 'courier_name', Arr::get($rate, 'courier', ''))),
                'service' => (string) (Arr::get($rate, 'courier_service_code', Arr::get($rate, 'service_code', ''))),
                'description' => (string) (Arr::get($rate, 'courier_service_name', Arr::get($rate, 'description', ''))),
                'etd' => $etd,
                'cost' => (int) round($price),
                'currency' => strtoupper((string) (Arr::get($rate, 'price.currency', 'IDR'))),
            ];
        }

        return [
            'destination' => $destination,
            'origin' => [
                'postal_code' => $config['origin_postal_code'],
                'area_id' => $config['origin_area_id'] ?? null,
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
        $config = $payload['config'] ?? [];
        $rate = $payload['rate'] ?? [];
        $contact = $payload['contact'] ?? [];
        $address = $payload['address'] ?? [];
        $items = $this->prepareItems($payload['items'] ?? Arr::get($payload, 'cart.items', []));

        if (empty($config['api_key'])) {
            throw new ShippingException('API key Biteship belum dikonfigurasi.');
        }

        if (empty($rate['courier']) || empty($rate['service'])) {
            throw new ShippingException('Pilihan kurir Biteship tidak valid.');
        }

        if (empty($items)) {
            throw new ShippingException('Daftar produk untuk pengiriman kosong.');
        }

        $requestPayload = $this->filterNull([
            'shipper_contact_name' => $config['origin_contact_name'] ?? 'Toko',
            'shipper_contact_phone' => $config['origin_contact_phone'] ?? null,
            'origin_contact_name' => $config['origin_contact_name'] ?? null,
            'origin_contact_phone' => $config['origin_contact_phone'] ?? null,
            'origin_address' => $config['origin_address'] ?? null,
            'origin_postal_code' => $config['origin_postal_code'] ?? null,
            'origin_area_id' => $config['origin_area_id'] ?? null,
            'origin_latitude' => $config['origin_latitude'] ?? null,
            'origin_longitude' => $config['origin_longitude'] ?? null,
            'destination_contact_name' => $contact['name'] ?? null,
            'destination_contact_phone' => $contact['phone'] ?? null,
            'destination_contact_email' => $contact['email'] ?? null,
            'destination_address' => $address['street'] ?? null,
            'destination_postal_code' => $address['postal_code'] ?? null,
            'courier_code' => strtolower((string) $rate['courier']),
            'courier_service_code' => $rate['service'],
            'items' => $items,
            'payment_type' => 'postpaid',
            'delivery_type' => 'regular',
            'metadata' => [
                'order_reference' => Arr::get($payload, 'reference'),
                'notes' => Arr::get($payload, 'notes'),
            ],
        ]);

        $response = $this->http($config)->post('/orders', $requestPayload);

        if ($response->failed()) {
            throw (new ShippingException($this->resolveErrorMessage($response) ?: 'Gagal membuat pesanan Biteship.'))
                ->withContext(['payload' => $requestPayload, 'response' => $response->json()]);
        }

        $data = $response->json();
        $price = Arr::get($data, 'price.total', Arr::get($data, 'price', Arr::get($data, 'pricing.total', $rate['cost'] ?? 0)));
        $currency = Arr::get($data, 'price.currency', Arr::get($data, 'currency', 'IDR'));

        return [
            'remote_id' => (string) (Arr::get($data, 'id') ?? Str::uuid()->toString()),
            'tracking_number' => (string) (Arr::get($data, 'waybill_id') ?? Arr::get($data, 'courier.waybill_id') ?? ''),
            'courier' => strtolower((string) ($rate['courier'] ?? Arr::get($data, 'courier.company'))),
            'service' => (string) ($rate['service'] ?? Arr::get($data, 'courier.service_code', '')),
            'description' => (string) ($rate['description'] ?? Arr::get($data, 'courier.service_name', '')),
            'cost' => (int) round($price ?? 0),
            'currency' => (string) $currency,
            'etd' => $this->formatEtd(Arr::get($data, 'courier', [])) ?: ($rate['etd'] ?? ''),
            'config' => $config,
            'rate' => $rate,
            'raw' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function track(string $trackingNumber, array $context = []): array
    {
        $config = $context['config'] ?? [];
        $courier = strtolower((string) ($context['courier'] ?? Arr::get($config, 'default_courier', '')));

        if (empty($config['api_key'])) {
            throw new ShippingException('API key Biteship belum dikonfigurasi.');
        }

        if ($courier === '') {
            throw new ShippingException('Kurir untuk pelacakan belum ditentukan.');
        }

        $response = $this->http($config)->get('/trackings/'.urlencode($trackingNumber), [
            'courier_code' => $courier,
        ]);

        if ($response->failed()) {
            throw (new ShippingException($this->resolveErrorMessage($response) ?: 'Gagal mengambil status pelacakan Biteship.'))
                ->withContext(['tracking_number' => $trackingNumber, 'response' => $response->json()]);
        }

        $data = $response->json();

        return [
            'summary' => [
                'status' => Arr::get($data, 'tracking_status.status'),
                'note' => Arr::get($data, 'tracking_status.note'),
                'updated_at' => Arr::get($data, 'tracking_status.updated_at'),
                'waybill' => Arr::get($data, 'waybill_id', $trackingNumber),
                'courier' => Arr::get($data, 'courier'),
            ],
            'history' => Arr::get($data, 'history', []),
            'raw' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function cancel(string $remoteId, array $context = []): array
    {
        $config = $context['config'] ?? [];

        if (empty($config['api_key'])) {
            throw new ShippingException('API key Biteship belum dikonfigurasi.');
        }

        if ($remoteId === '') {
            throw new ShippingException('ID pesanan Biteship tidak valid.');
        }

        $response = $this->http($config)->post('/orders/'.urlencode($remoteId).'/cancel');

        if ($response->failed()) {
            throw (new ShippingException($this->resolveErrorMessage($response) ?: 'Gagal membatalkan pesanan Biteship.'))
                ->withContext(['remote_id' => $remoteId, 'response' => $response->json()]);
        }

        return [
            'remote_id' => $remoteId,
            'status' => Arr::get($response->json(), 'status', 'cancelled'),
            'message' => Arr::get($response->json(), 'message', 'Pesanan Biteship dibatalkan.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function prepareItems(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                $quantity = (int) ($item['quantity'] ?? 1);
                $weightKg = (float) ($item['weight'] ?? config('shipping.default_weight', 1));

                return $this->filterNull([
                    'name' => $item['name'] ?? 'Produk',
                    'value' => (int) round(($item['price'] ?? 0) * $quantity),
                    'weight' => max(1, (int) round($weightKg * 1000)),
                    'quantity' => max(1, $quantity),
                ]);
            })
            ->filter(fn ($item) => ! empty($item['name']))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>|Response  $response
     */
    protected function resolveErrorMessage(Response $response): string
    {
        $json = $response->json();

        $message = Arr::get($json, 'message')
            ?? Arr::get($json, 'error')
            ?? Arr::get($json, 'errors.0.message')
            ?? $response->reason();

        return (string) $message;
    }

    protected function formatEtd(array $rate): string
    {
        $duration = Arr::get($rate, 'duration', []);
        $delivery = Arr::get($duration, 'delivery') ?? Arr::get($rate, 'etd');

        if (is_array($delivery)) {
            $min = Arr::get($delivery, 'min');
            $max = Arr::get($delivery, 'max');
            if ($min && $max) {
                return $min.'-'.$max.' hari';
            }
            if ($min || $max) {
                return ($min ?? $max).' hari';
            }
        }

        if (is_string($delivery) && $delivery !== '') {
            return $delivery;
        }

        return '';
    }

    protected function baseUrl(array $config): string
    {
        return 'https://api.biteship.com/v1';
    }

    protected function http(array $config): PendingRequest
    {
        $apiKey = (string) ($config['api_key'] ?? '');

        return Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(30)
            ->baseUrl($this->baseUrl($config));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function filterNull(array $payload): array
    {
        return collect($payload)
            ->reject(function ($value) {
                if (is_array($value)) {
                    return false;
                }

                return $value === null || $value === '';
            })
            ->map(function ($value) {
                if (is_array($value)) {
                    return $this->filterNull($value);
                }

                return $value;
            })
            ->all();
    }
}
