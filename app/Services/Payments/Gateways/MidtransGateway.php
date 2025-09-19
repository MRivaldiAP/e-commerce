<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\PaymentGateway;

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
            ],
            'bank_transfer' => [
                'label' => 'Transfer Bank',
                'description' => 'Virtual account bank seperti BCA, Mandiri, BNI, dan lainnya.',
                'default' => true,
            ],
            'credit_card' => [
                'label' => 'Kartu Kredit/Debit',
                'description' => 'Visa, Mastercard, JCB dan kartu lainnya.',
            ],
            'gopay' => [
                'label' => 'GoPay',
                'description' => 'Dompet digital GoPay.',
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
}
