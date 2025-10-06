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
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function checkRates(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array;

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function track(string $trackingNumber, array $context = []): array;

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function cancel(string $remoteId, array $context = []): array;
}
