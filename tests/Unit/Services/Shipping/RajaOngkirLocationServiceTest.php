<?php

namespace Tests\Unit\Services\Shipping;

use App\Services\Shipping\RajaOngkirLocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirLocationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected string $regencyCode = '31.73';

    protected string $districtCode = '31.73.01';

    public function test_it_syncs_city_locations_from_rajaongkir(): void
    {
        Http::fake([
            'https://api.rajaongkir.com/starter/city' => Http::response([
                'rajaongkir' => [
                    'results' => [
                        [
                            'city_id' => '501',
                            'city_name' => 'Jakarta Barat',
                            'type' => 'Kota',
                            'province' => 'DKI Jakarta',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(RajaOngkirLocationService::class);
        $service->sync('test-key', 'starter', true);

        $this->assertDatabaseHas('raja_ongkir_locations', [
            'remote_id' => '501',
            'level' => 'city',
            'name' => 'Jakarta Barat',
            'nusa_regency_code' => $this->regencyCode,
        ]);

        $options = $service->originOptions('starter', 'city');
        $this->assertCount(1, $options);
        $this->assertSame('501', $options[0]['value']);
        $this->assertStringContainsString('Jakarta Barat', $options[0]['label']);

        $location = $service->findCityByRegencyCode($this->regencyCode);
        $this->assertNotNull($location);
        $this->assertSame('501', $location->remote_id);
    }

    public function test_it_syncs_subdistricts_for_pro_accounts(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/city')) {
                return Http::response([
                    'rajaongkir' => [
                        'results' => [
                            [
                                'city_id' => '501',
                                'city_name' => 'Jakarta Barat',
                                'type' => 'Kota',
                                'province' => 'DKI Jakarta',
                            ],
                        ],
                    ],
                ], 200);
            }

            if (str_contains($request->url(), '/subdistrict')) {
                return Http::response([
                    'rajaongkir' => [
                        'results' => [
                            [
                                'subdistrict_id' => '50101',
                                'subdistrict_name' => 'Cengkareng',
                                'city' => 'Jakarta Barat',
                                'province' => 'DKI Jakarta',
                                'type' => 'Kecamatan',
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $service = app(RajaOngkirLocationService::class);
        $service->sync('test-key', 'pro', true);

        $this->assertDatabaseHas('raja_ongkir_locations', [
            'remote_id' => '50101',
            'level' => 'subdistrict',
            'name' => 'Cengkareng',
            'nusa_district_code' => $this->districtCode,
        ]);

        $options = $service->originOptions('pro', 'subdistrict');
        $this->assertCount(1, $options);
        $this->assertSame('50101', $options[0]['value']);
        $this->assertStringContainsString('Cengkareng', $options[0]['label']);

        $location = $service->findSubdistrictByDistrictCode($this->districtCode);
        $this->assertNotNull($location);
        $this->assertSame('50101', $location->remote_id);
    }
}
