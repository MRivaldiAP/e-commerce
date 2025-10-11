<?php

namespace App\Console\Commands;

use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\RajaOngkirLocationSyncService;
use App\Services\Shipping\ShippingGatewayManager;
use Illuminate\Console\Command;

class SyncRajaOngkirLocations extends Command
{
    protected $signature = 'rajaongkir:sync-locations {--api-key=} {--account-type=}';

    protected $description = 'Sinkronisasi data kota dan kecamatan dari RajaOngkir.';

    public function handle(ShippingGatewayManager $shipping, RajaOngkirLocationSyncService $sync): int
    {
        $config = $shipping->getGatewayConfig('rajaongkir');

        if ($apiKey = $this->option('api-key')) {
            $config['api_key'] = $apiKey;
        }

        if ($accountType = $this->option('account-type')) {
            $config['account_type'] = $accountType;
        }

        try {
            $result = $sync->sync($config);
        } catch (ShippingException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Sinkronisasi selesai. %d kota dan %d kecamatan diperbarui.',
            $result['cities'],
            $result['subdistricts']
        ));

        return self::SUCCESS;
    }
}
