<?php

namespace App\Services\Shipping\Gateways;

use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\ShippingGateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class RajaOngkirGateway implements ShippingGateway
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
        return 'Integrasi layanan pengiriman domestik menggunakan RajaOngkir.';
    }

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
                'key' => 'account_type',
                'label' => 'Tipe Akun',
                'type' => 'select',
                'options' => [
                    'starter' => 'Starter',
                    'basic' => 'Basic',
                    'pro' => 'Pro',
                ],
                'default' => 'starter',
                'rules' => 'required|string|in:starter,basic,pro',
            ],
            [
                'key' => 'origin_type',
                'label' => 'Tipe Origin',
                'type' => 'select',
                'options' => [
                    'city' => 'Kota/Kabupaten',
                    'subdistrict' => 'Kecamatan',
                ],
                'default' => 'city',
                'rules' => 'required|string|in:city,subdistrict',
            ],
            [
                'key' => 'origin',
                'label' => 'Kode Origin RajaOngkir',
                'type' => 'text',
                'placeholder' => 'Contoh: 501 (kode kota/kecamatan)',
                'rules' => 'required|string',
            ],
            [
                'key' => 'enabled_couriers',
                'label' => 'Kurir Diaktifkan',
                'type' => 'text',
                'default' => 'jne,pos,tiki',
                'help' => 'Pisahkan dengan koma, contoh: jne,pos,tiki,jnt',
            ],
        ];
    }

    public function fetchRates(array $config, array $payload): array
    {
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $destination = Arr::get($payload, 'destination', []);
        $destinationCode = (string) Arr::get($destination, 'code');
        $destinationType = (string) Arr::get($destination, 'type', 'city');
        $weight = (int) max(1, (int) ($payload['weight'] ?? 1));

        if ($apiKey === '' || $destinationCode === '') {
            return [];
        }

        $origin = (string) ($config['origin'] ?? '');
        $originType = (string) ($config['origin_type'] ?? 'city');
        $courier = $this->normalizeCouriers($config['enabled_couriers'] ?? 'jne,pos,tiki');

        try {
            $response = Http::withHeaders(['key' => $apiKey])
                ->timeout(15)
                ->post($this->baseUrl($config) . '/cost', [
                    'origin' => $origin,
                    'originType' => $originType,
                    'destination' => $destinationCode,
                    'destinationType' => $destinationType,
                    'weight' => $weight,
                    'courier' => $courier,
                ]);
        } catch (\Throwable $exception) {
            throw new ShippingException($exception->getMessage(), (int) $exception->getCode());
        }

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description', 'Gagal mengambil tarif pengiriman.');
            throw new ShippingException($message, $response->status());
        }

        $results = Arr::get($response->json(), 'rajaongkir.results', []);
        $rates = [];

        foreach ($results as $result) {
            $code = strtoupper((string) Arr::get($result, 'code', ''));
            $name = (string) Arr::get($result, 'name', $code);
            $services = Arr::get($result, 'costs', []);

            foreach ($services as $service) {
                $serviceCode = (string) Arr::get($service, 'service', '');
                $description = (string) Arr::get($service, 'description', '');
                $costs = Arr::get($service, 'cost', []);

                foreach ($costs as $cost) {
                    $value = (float) Arr::get($cost, 'value', 0);
                    $etd = Arr::get($cost, 'etd');
                    $rates[] = [
                        'provider' => $this->key(),
                        'courier' => $code,
                        'courier_name' => $name,
                        'service' => $serviceCode,
                        'description' => $description,
                        'cost' => $value,
                        'etd' => $etd,
                        'note' => Arr::get($cost, 'note'),
                    ];
                }
            }
        }

        return $rates;
    }

    public function createShipment(array $config, array $payload): array
    {
        return [
            'status' => Arr::get($payload, 'status', 'packing'),
            'message' => 'Permintaan pengiriman dicatat. Tambahkan nomor resi untuk melacak pengiriman.',
        ];
    }

    public function cancelShipment(array $config, array $payload): array
    {
        return [
            'status' => 'cancelled',
            'message' => 'Pengiriman dibatalkan dari sistem toko.',
        ];
    }

    public function trackShipment(array $config, array $context): array
    {
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $trackingNumber = (string) Arr::get($context, 'tracking_number', Arr::get($context, 'awb'));
        $courier = $this->extractTrackingCourier($context, $config);

        if ($apiKey === '' || $trackingNumber === '' || $courier === '') {
            throw new ShippingException('Data pelacakan tidak lengkap.');
        }

        try {
            $response = Http::withHeaders(['key' => $apiKey])
                ->timeout(15)
                ->post($this->baseUrl($config) . '/waybill', [
                    'waybill' => $trackingNumber,
                    'courier' => $courier,
                ]);
        } catch (\Throwable $exception) {
            throw new ShippingException($exception->getMessage(), (int) $exception->getCode());
        }

        if ($response->failed()) {
            $message = Arr::get($response->json(), 'rajaongkir.status.description', 'Gagal melacak nomor resi.');
            throw new ShippingException($message, $response->status());
        }

        $result = Arr::get($response->json(), 'rajaongkir.result', []);
        $deliveryStatus = Arr::get($result, 'delivery_status.status', '');

        return [
            'status' => $this->normalizeStatus($deliveryStatus),
            'summary' => Arr::get($result, 'summary', []),
            'details' => Arr::get($result, 'details', []),
            'delivery_status' => Arr::get($result, 'delivery_status', []),
            'manifest' => Arr::get($result, 'manifest', []),
        ];
    }

    protected function baseUrl(array $config): string
    {
        return match (strtolower((string) ($config['account_type'] ?? 'starter'))) {
            'pro' => 'https://pro.rajaongkir.com/api',
            'basic' => 'https://api.rajaongkir.com/basic',
            default => 'https://api.rajaongkir.com/starter',
        };
    }

    protected function normalizeCouriers(string $couriers): string
    {
        $normalized = strtolower(str_replace([';', ','], ':', $couriers));
        $normalized = preg_replace('/[^a-z:]/', '', $normalized ?? '') ?? '';

        return trim($normalized, ':') ?: 'jne:pos:tiki';
    }

    protected function extractTrackingCourier(array $context, array $config): string
    {
        $courier = strtolower((string) Arr::get($context, 'courier'));
        if ($courier !== '') {
            return $courier;
        }

        $couriers = explode(':', $this->normalizeCouriers($config['enabled_couriers'] ?? 'jne'));

        return strtolower((string) Arr::first($couriers)) ?: 'jne';
    }

    protected function normalizeStatus(string $status): string
    {
        $status = strtolower($status);

        return match (true) {
            str_contains($status, 'deliver') => 'delivered',
            str_contains($status, 'transit'), str_contains($status, 'process') => 'in_transit',
            str_contains($status, 'cancel') => 'cancelled',
            default => 'packing',
        };
    }
}
