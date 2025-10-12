<?php

namespace App\Services\Shipping;

use App\Models\Setting;
use Illuminate\Support\Arr;

class ShippingGatewayManager
{
    /**
     * @var array<string, \App\Services\Shipping\ShippingGateway>
     */
    protected array $gateways = [];

    public function __construct()
    {
        $configured = config('shipping.gateways', []);

        foreach ($configured as $class) {
            $instance = app($class);
            if ($instance instanceof ShippingGateway) {
                $this->gateways[$instance->key()] = $instance;
            }
        }
    }

    /**
     * @return array<string, ShippingGateway>
     */
    public function all(): array
    {
        return $this->gateways;
    }

    public function get(string $key): ?ShippingGateway
    {
        return $this->gateways[$key] ?? null;
    }

    public function getActiveKey(): ?string
    {
        $key = Setting::getValue('shipping.provider');

        return $key ?: null;
    }

    public function getActive(): ?ShippingGateway
    {
        $key = $this->getActiveKey();

        return $key ? $this->get($key) : null;
    }

    public function isEnabled(): bool
    {
        return Setting::getValue('shipping.enabled', '0') === '1';
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAllGatewayConfigs(): array
    {
        $configs = [];
        foreach ($this->gateways as $key => $gateway) {
            $configs[$key] = $this->getGatewayConfig($key);
        }

        return $configs;
    }

    /**
     * @return array<string, mixed>
     */
    public function getGatewayConfig(string $gatewayKey): array
    {
        $gateway = $this->get($gatewayKey);
        if (! $gateway) {
            return [];
        }

        $config = [];
        foreach ($gateway->configFields() as $field) {
            $fieldKey = $field['key'];
            $default = $field['default'] ?? null;
            $value = Setting::getValue($this->configKey($gatewayKey, $fieldKey), $default);

            if ($this->isBooleanField($field)) {
                $config[$fieldKey] = $this->castToBool($value, $default);
            } elseif ($this->isArrayField($field)) {
                if (is_array($value)) {
                    $config[$fieldKey] = $value;
                } else {
                    $decoded = json_decode((string) $value, true);
                    $config[$fieldKey] = is_array($decoded) ? $decoded : (is_array($default) ? $default : []);
                }
            } else {
                $config[$fieldKey] = $value;
            }
        }

        return $config;
    }

    public function storeGateway(string $gatewayKey): void
    {
        Setting::updateOrCreate(
            ['key' => 'shipping.provider'],
            ['value' => $gatewayKey]
        );
    }

    public function storeConfig(string $gatewayKey, array $values): void
    {
        $gateway = $this->get($gatewayKey);
        if (! $gateway) {
            return;
        }

        foreach ($gateway->configFields() as $field) {
            $fieldKey = $field['key'];
            $value = $values[$fieldKey] ?? ($field['default'] ?? null);

            if ($this->isBooleanField($field)) {
                $value = $value ? '1' : '0';
            } elseif ($this->isArrayField($field)) {
                $value = json_encode(array_values(Arr::wrap($value)));
            }

            Setting::updateOrCreate(
                ['key' => $this->configKey($gatewayKey, $fieldKey)],
                ['value' => $value ?? '']
            );
        }
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function isBooleanField(array $field): bool
    {
        $type = $field['type'] ?? null;

        return in_array($type, ['boolean', 'toggle', 'checkbox'], true);
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function isArrayField(array $field): bool
    {
        if (($field['multiple'] ?? false) === true) {
            return true;
        }

        $type = $field['type'] ?? null;

        return in_array($type, ['array', 'list', 'multiselect'], true);
    }

    protected function configKey(string $gatewayKey, string $fieldKey): string
    {
        return "shipping.{$gatewayKey}.{$fieldKey}";
    }

    protected function castToBool(mixed $value, mixed $default = null): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_null($value) && ! is_null($default)) {
            return $this->castToBool($default);
        }

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }
}
