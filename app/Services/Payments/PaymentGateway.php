<?php

namespace App\Services\Payments;

interface PaymentGateway
{
    /**
     * Unique identifier for the gateway.
     */
    public function key(): string;

    /**
     * Human readable name for the gateway.
     */
    public function label(): string;

    /**
     * Short description shown in the admin.
     */
    public function description(): string;

    /**
     * Configuration fields required for the gateway.
     *
     * @return array<int, array<string, mixed>>
     */
    public function configFields(): array;

    /**
     * Available payment methods supported by the gateway.
     * The return value should be an associative array where the key is the method identifier.
     *
     * @return array<string, array<string, mixed>>
     */
    public function availableMethods(): array;

    /**
     * Data that should be shown on the checkout page when this gateway is active.
     *
     * @param  array<string, mixed>  $config
     * @param  array<int, array<string, mixed>>  $selectedMethods
     * @param  array<string, mixed>  $cartSummary
     * @return array<string, mixed>
     */
    public function checkoutData(array $config, array $selectedMethods, array $cartSummary): array;
}
