<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'midtrans';
    }

    public function label(): string
    {
        return 'Midtrans';
    }

    public function description(): string
    {
        return 'Integrasi pembayaran Midtrans Snap dengan berbagai metode pembayaran populer.';
    }

    public function configFields(): array
    {
        return [
            [
                'key' => 'server_key',
                'label' => 'Server Key',
                'type' => 'password',
                'rules' => 'required|string',
                'sensitive' => true,
                'help' => 'Ambil server key dari dashboard Midtrans Anda.',
            ],
            [
                'key' => 'client_key',
                'label' => 'Client Key',
                'type' => 'text',
                'rules' => 'required|string',
                'help' => 'Client key diperlukan untuk memuat Snap di sisi front-end.',
            ],
            [
                'key' => 'environment',
                'label' => 'Mode Transaksi',
                'type' => 'select',
                'options' => [
                    'sandbox' => 'Sandbox',
                    'production' => 'Production',
                ],
                'default' => 'sandbox',
                'rules' => 'required|in:sandbox,production',
            ],
        ];
    }

    public function availableMethods(): array
    {
        return [
            'qris' => [
                'label' => 'QRIS',
                'description' => 'Pembayaran cepat melalui QRIS.',
                'default' => true,
                'snap_codes' => ['qris'],
            ],
            'bank_transfer' => [
                'label' => 'Transfer Bank',
                'description' => 'Virtual account bank seperti BCA, Mandiri, BNI, dan lainnya.',
                'default' => true,
                'snap_codes' => ['bank_transfer'],
            ],
            'credit_card' => [
                'label' => 'Kartu Kredit/Debit',
                'description' => 'Visa, Mastercard, JCB dan kartu lainnya.',
                'snap_codes' => ['credit_card'],
            ],
            'gopay' => [
                'label' => 'GoPay',
                'description' => 'Dompet digital GoPay.',
                'snap_codes' => ['gopay'],
            ],
        ];
    }

    public function checkoutData(array $config, array $selectedMethods, array $cartSummary): array
    {
        return [
            'title' => 'Bayar dengan Midtrans',
            'subtitle' => 'Midtrans Snap akan menangani proses pembayaran Anda.',
            'instructions' => [
                'Periksa kembali detail pesanan Anda di bawah ini.',
                'Pilih metode pembayaran Midtrans yang ingin digunakan.',
                'Klik tombol "Bayar dengan Midtrans" untuk membuka Snap dan selesaikan pembayaran.',
            ],
            'publicConfig' => [
                'client_key' => $config['client_key'] ?? null,
                'environment' => $config['environment'] ?? 'sandbox',
                'supported_methods' => array_values(array_filter(array_map(
                    fn (array $method) => $method['key'] ?? null,
                    $selectedMethods
                ))),
            ],
        ];
    }

    public function createPaymentSession(array $config, array $cart, array $context = []): array
    {
        $serverKey = $config['server_key'] ?? null;
        if (! $serverKey) {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $environment = ($config['environment'] ?? 'sandbox') === 'production' ? 'production' : 'sandbox';
        $baseUrl = $environment === 'production'
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $items = [];
        $rawItems = Arr::get($cart, 'items', []);
        foreach ($rawItems as $index => $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $price = (int) ($item['price'] ?? 0);
            $items[] = [
                'id' => (string) ($item['product_id'] ?? ('item-' . ($index + 1))),
                'name' => substr((string) ($item['name'] ?? 'Item ' . ($index + 1)), 0, 50),
                'price' => $price,
                'quantity' => $quantity,
            ];
        }

        if (empty($items)) {
            throw new RuntimeException('Tidak ada item di keranjang.');
        }

        $grossAmount = (int) ($cart['total_price'] ?? 0);
        if ($grossAmount <= 0) {
            $grossAmount = array_reduce($items, fn ($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0);
        }

        if ($grossAmount <= 0) {
            throw new RuntimeException('Total belanja tidak valid.');
        }

        $selectedMethod = $context['selected_method'] ?? null;
        $enabledMethods = $context['enabled_methods'] ?? [];
        $enabledPayments = $this->resolveSnapPaymentCodes($enabledMethods, $selectedMethod);

        $orderId = $context['reference'] ?? ('INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)));

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'credit_card' => ['secure' => true],
        ];

        if (! empty($enabledPayments)) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        $callbacks = [
            'finish' => $context['finish_url'] ?? $context['success_url'] ?? url('/checkout/payment'),
            'error' => $context['error_url'] ?? url('/checkout/payment'),
            'pending' => $context['pending_url'] ?? url('/checkout/payment'),
        ];

        $payload['callbacks'] = $callbacks;

        if (! empty($context['customer']) && is_array($context['customer'])) {
            $payload['customer_details'] = array_filter([
                'first_name' => $context['customer']['first_name'] ?? null,
                'last_name' => $context['customer']['last_name'] ?? null,
                'email' => $context['customer']['email'] ?? null,
                'phone' => $context['customer']['phone'] ?? null,
            ]);
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->post($baseUrl . '/snap/v1/transactions', $payload);

            if ($response->failed()) {
                $message = $response->json('error_messages.0')
                    ?? $response->json('status_message')
                    ?? 'Midtrans mengembalikan respon yang tidak valid.';
                throw new RuntimeException((string) $message);
            }

            $data = $response->json();
            $redirectUrl = $data['redirect_url'] ?? null;
            $token = $data['token'] ?? null;

            if (! $redirectUrl || ! $token) {
                throw new RuntimeException('Midtrans tidak mengembalikan tautan pembayaran.');
            }

            return [
                'type' => 'redirect',
                'redirect_url' => $redirectUrl,
                'token' => $token,
                'reference' => $orderId,
                'environment' => $environment,
            ];
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Midtrans checkout gagal', [
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            throw new RuntimeException('Gagal membuat sesi pembayaran Midtrans.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $enabledMethods
     * @return array<int, string>
     */
    protected function resolveSnapPaymentCodes(array $enabledMethods, ?string $selectedMethod): array
    {
        $candidates = [];

        if ($selectedMethod) {
            foreach ($enabledMethods as $method) {
                if (($method['key'] ?? null) === $selectedMethod) {
                    $candidates[] = $method;
                    break;
                }
            }
        }

        if (empty($candidates)) {
            $candidates = $enabledMethods;
        }

        $codes = [];
        foreach ($candidates as $method) {
            $snapCodes = $method['snap_codes'] ?? ($method['key'] ?? null);
            if (is_array($snapCodes)) {
                foreach ($snapCodes as $code) {
                    if (is_string($code)) {
                        $codes[] = $code;
                    }
                }
            } elseif (is_string($snapCodes)) {
                $codes[] = $snapCodes;
            }
        }

        return array_values(array_unique(array_filter($codes)));
    }
}
