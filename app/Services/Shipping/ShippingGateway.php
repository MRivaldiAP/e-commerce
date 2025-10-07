<?php

namespace App\Services\Shipping;

interface ShippingGateway
{
    public function key(): string;

    public function label(): string;

    public function description(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function configFields(): array;

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    public function fetchRates(array $config, array $payload): array;

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createShipment(array $config, array $payload): array;

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function cancelShipment(array $config, array $payload): array;

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function trackShipment(array $config, array $context): array;
}
