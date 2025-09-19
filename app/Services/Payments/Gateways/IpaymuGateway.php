<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\PaymentGateway;

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
            ],
            'qris' => [
                'label' => 'QRIS',
                'description' => 'Pembayaran menggunakan QRIS.',
                'default' => true,
            ],
            'cstore' => [
                'label' => 'Convenience Store',
                'description' => 'Pembayaran melalui gerai Alfamart dan Indomaret.',
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
}
