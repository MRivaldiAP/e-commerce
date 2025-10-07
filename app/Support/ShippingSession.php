<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class ShippingSession
{
    public const SESSION_KEY = 'checkout.shipping';

    public static function get(): array
    {
        $stored = Session::get(self::SESSION_KEY, []);

        return is_array($stored) ? $stored : [];
    }

    public static function store(array $data): void
    {
        $payload = [
            'contact' => Arr::get($data, 'contact', []),
            'address' => Arr::get($data, 'address', []),
            'selection' => Arr::get($data, 'selection', []),
            'cost' => (float) Arr::get($data, 'cost', 0),
            'total' => (float) Arr::get($data, 'total', 0),
            'provider' => Arr::get($data, 'provider'),
            'metadata' => Arr::get($data, 'metadata', []),
        ];

        Session::put(self::SESSION_KEY, $payload);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function isReady(): bool
    {
        $data = self::get();

        return ! empty($data['selection']) && ! empty($data['address']);
    }
}
