<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class IpaymuGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'ipaymu';
    }

    public function label(): string
    {
        return 'iPaymu';
    }

    public function description(): string
    {
        return 'iPaymu menyediakan pembayaran virtual account, gerai retail, dan QRIS.';
    }

    public function configFields(): array
    {
        return [
            [
                'key' => 'va',
                'label' => 'Virtual Account',
                'type' => 'text',
                'rules' => 'required|string',
                'help' => 'Masukkan nomor virtual account merchant iPaymu Anda.',
            ],
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'rules' => 'required|string',
                'sensitive' => true,
                'help' => 'Gunakan API key dari dashboard iPaymu.',
            ],
            [
                'key' => 'sandbox',
                'label' => 'Mode Sandbox',
                'type' => 'toggle',
                'default' => true,
                'rules' => 'boolean',
            ],
        ];
    }

    public function availableMethods(): array
    {
        return [
            'va' => [
                'label' => 'Virtual Account',
                'description' => 'Virtual account bank di jaringan iPaymu.',
                'default' => true,
                'ipaymu_code' => 'va',
            ],
            'qris' => [
                'label' => 'QRIS',
                'description' => 'Pembayaran menggunakan QRIS.',
                'default' => true,
                'ipaymu_code' => 'qris',
            ],
            'cstore' => [
                'label' => 'Convenience Store',
                'description' => 'Pembayaran melalui gerai Alfamart dan Indomaret.',
                'ipaymu_code' => 'cstore',
            ],
        ];
    }

    public function checkoutData(array $config, array $selectedMethods, array $cartSummary): array
    {
        return [
            'title' => 'Bayar melalui iPaymu',
            'subtitle' => 'Gunakan iPaymu untuk menyelesaikan pembayaran Anda.',
            'instructions' => [
                'Tinjau kembali ringkasan pesanan di bawah ini.',
                'Pilih metode pembayaran iPaymu yang tersedia.',
                'Tekan tombol "Bayar via iPaymu" untuk membuat instruksi pembayaran.',
            ],
            'publicConfig' => [
                'va' => $config['va'] ?? null,
                'sandbox' => (bool) ($config['sandbox'] ?? false),
                'supported_methods' => array_values(array_filter(array_map(
                    fn (array $method) => $method['key'] ?? null,
                    $selectedMethods
                ))),
            ],
        ];
    }

    public function createPaymentSession(array $config, array $cart, array $context = []): array
    {
        $va = $config['va'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        if (! $va || ! $apiKey) {
            throw new RuntimeException('Konfigurasi iPaymu belum lengkap.');
        }

        $sandbox = ($config['sandbox'] ?? true) ? true : false;
        $baseUrl = $sandbox ? 'https://sandbox.ipaymu.com' : 'https://my.ipaymu.com';

        $items = Arr::get($cart, 'items', []);
        if (empty($items)) {
            throw new RuntimeException('Tidak ada item di keranjang.');
        }

        $products = [];
        $quantities = [];
        $prices = [];
        $descriptions = [];
        foreach ($items as $item) {
            $name = (string) ($item['name'] ?? 'Produk');
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $price = (int) ($item['price'] ?? 0);

            $products[] = $name;
            $quantities[] = (string) $quantity;
            $prices[] = (string) $price;
            $descriptions[] = $name;
        }

        $grossAmount = (int) ($cart['total_price'] ?? 0);
        if ($grossAmount <= 0) {
            $grossAmount = array_reduce($items, fn ($carry, $item) => $carry + ((int) ($item['price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1))), 0);
        }

        if ($grossAmount <= 0) {
            throw new RuntimeException('Total belanja tidak valid.');
        }

        $selectedMethod = $context['selected_method'] ?? null;
        $enabledMethods = $context['enabled_methods'] ?? [];
        $methodCode = $this->resolveIpaymuMethod($enabledMethods, $selectedMethod);

        $orderId = $context['reference'] ?? ('INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)));

        $body = [
            'product' => $products,
            'qty' => $quantities,
            'price' => $prices,
            'description' => $descriptions,
            'returnUrl' => $context['success_url'] ?? route('checkout.payment', ['status' => 'success']),
            'cancelUrl' => $context['cancel_url'] ?? route('checkout.payment', ['status' => 'cancelled']),
            'notifyUrl' => $context['notify_url'] ?? route('checkout.payment.webhook', ['gateway' => $this->key()]),
            'referenceId' => $orderId,
            'amount' => (string) $grossAmount,
        ];

        if ($methodCode) {
            $body['paymentMethod'] = $methodCode;
        }

        if (! empty($context['customer']) && is_array($context['customer'])) {
            $body['buyerName'] = trim(($context['customer']['first_name'] ?? '') . ' ' . ($context['customer']['last_name'] ?? '')) ?: null;
            $body['buyerEmail'] = $context['customer']['email'] ?? null;
            $body['buyerPhone'] = $context['customer']['phone'] ?? null;
        }

        $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        if ($jsonBody === false) {
            throw new RuntimeException('Gagal mempersiapkan data pembayaran iPaymu.');
        }

        $requestBody = strtolower(hash('sha256', $jsonBody));
        $method = 'POST';
        $stringToSign = strtoupper($method) . ':' . $va . ':' . $requestBody . ':' . $apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $apiKey);
        $timestamp = now()->format('YmdHis');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'va' => $va,
                'signature' => $signature,
                'timestamp' => $timestamp,
            ])->post($baseUrl . '/api/v2/payment', $body);

            if ($response->failed()) {
                $message = $response->json('Message') ?? $response->json('message') ?? 'iPaymu mengembalikan respon yang tidak valid.';
                throw new RuntimeException((string) $message);
            }

            $data = $response->json();
            $url = Arr::get($data, 'Data.Url') ?? Arr::get($data, 'Data.PaymentUrl');

            if (! $url) {
                throw new RuntimeException('iPaymu tidak mengembalikan URL pembayaran.');
            }

            return [
                'type' => 'redirect',
                'redirect_url' => $url,
                'reference' => $orderId,
                'environment' => $sandbox ? 'sandbox' : 'production',
            ];
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('iPaymu checkout gagal', [
                'message' => $exception->getMessage(),
                'body' => $body,
            ]);

            throw new RuntimeException('Gagal membuat sesi pembayaran iPaymu.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $enabledMethods
     */
    protected function resolveIpaymuMethod(array $enabledMethods, ?string $selectedMethod): ?string
    {
        if ($selectedMethod) {
            foreach ($enabledMethods as $method) {
                if (($method['key'] ?? null) === $selectedMethod) {
                    return $method['ipaymu_code'] ?? $method['key'];
                }
            }
        }

        foreach ($enabledMethods as $method) {
            if (($method['default'] ?? false) === true) {
                return $method['ipaymu_code'] ?? $method['key'] ?? null;
            }
        }

        $first = reset($enabledMethods);

        return is_array($first) ? ($first['ipaymu_code'] ?? $first['key'] ?? null) : null;
    }
}
