<?php

namespace Tests\Unit\Services\Shipping;

use App\Services\Shipping\BiteshipShippingGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BiteshipShippingGatewayTest extends TestCase
{
    public function test_it_parses_rates_from_biteship_response(): void
    {
        Http::fake([
            'https://api.biteship.com/v1/rates/couriers' => Http::response([
                'pricing' => [
                    [
                        'courier' => 'jne',
                        'courier_name' => 'JNE',
                        'courier_service_code' => 'REG',
                        'courier_service_name' => 'Regular',
                        'price' => [
                            'total' => 12000,
                            'currency' => 'IDR',
                        ],
                        'duration' => [
                            'delivery' => [
                                'min' => 2,
                                'max' => 3,
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $gateway = new BiteshipShippingGateway();

        $result = $gateway->checkRates([
            'config' => [
                'api_key' => 'test-key',
                'origin_postal_code' => '12345',
                'couriers' => ['jne'],
            ],
            'destination' => [
                'postal_code' => '54321',
            ],
            'weight' => 1500,
            'subtotal' => 50000,
        ]);

        $this->assertArrayHasKey('rates', $result);
        $this->assertCount(1, $result['rates']);
        $rate = $result['rates'][0];

        $this->assertSame('jne', $rate['courier']);
        $this->assertSame('REG', $rate['service']);
        $this->assertSame(12000, $rate['cost']);
        $this->assertSame('2-3 hari', $rate['etd']);
    }

    public function test_it_creates_order_and_returns_summary(): void
    {
        Http::fake([
            'https://api.biteship.com/v1/orders' => Http::response([
                'id' => 'order-123',
                'waybill_id' => 'WB123',
                'courier' => [
                    'company' => 'jne',
                    'service_code' => 'REG',
                    'service_name' => 'Regular',
                    'duration' => [
                        'delivery' => [
                            'min' => 1,
                            'max' => 2,
                        ],
                    ],
                ],
                'price' => [
                    'total' => 14000,
                    'currency' => 'IDR',
                ],
            ], 200),
        ]);

        $gateway = new BiteshipShippingGateway();

        $result = $gateway->createOrder([
            'config' => [
                'api_key' => 'test-key',
                'origin_contact_name' => 'Admin',
                'origin_contact_phone' => '08123456789',
                'origin_address' => 'Jl. Sudirman No.1',
                'origin_postal_code' => '12345',
            ],
            'rate' => [
                'courier' => 'jne',
                'service' => 'REG',
                'description' => 'Reguler',
                'cost' => 14000,
                'etd' => '1-2',
            ],
            'contact' => [
                'name' => 'Budi',
                'email' => 'budi@example.com',
                'phone' => '08987654321',
            ],
            'address' => [
                'street' => 'Jl. Mawar No. 5',
                'postal_code' => '54321',
            ],
            'items' => [
                [
                    'name' => 'Produk A',
                    'price' => 50000,
                    'quantity' => 1,
                    'weight' => 1.5,
                ],
            ],
        ]);

        $this->assertSame('order-123', $result['remote_id']);
        $this->assertSame('WB123', $result['tracking_number']);
        $this->assertSame(14000, $result['cost']);
        $this->assertSame('IDR', $result['currency']);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $this->assertSame('postpaid', $payload['payment_type']);
            $this->assertSame('regular', $payload['delivery_type']);
            $this->assertSame('jne', $payload['courier_code']);
            $this->assertSame('REG', $payload['courier_service_code']);
            $this->assertCount(1, $payload['items']);

            return true;
        });
    }
}
