<?php

return [
    'connection' => env('CREASI_NUSA_CONNECTION', 'nusa'),

    'table_names' => [
        'provinces' => 'provinces',
        'districts' => 'districts',
        'regencies' => 'regencies',
        'villages' => 'villages',
    ],

    'addressable' => \Creasi\Nusa\Models\Address::class,

    'routes_enable' => env('CREASI_NUSA_ROUTES_ENABLE', false),

    'routes_prefix' => env('CREASI_NUSA_ROUTES_PREFIX', 'nusa'),
];
