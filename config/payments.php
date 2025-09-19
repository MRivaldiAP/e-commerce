<?php

return [
    'gateways' => [
        \App\Services\Payments\Gateways\MidtransGateway::class,
        \App\Services\Payments\Gateways\IpaymuGateway::class,
    ],
];
