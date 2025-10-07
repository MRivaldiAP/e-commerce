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

    public function get(?string $key = null): ?ShippingGateway
    {
        if (! $key) {
            return null;
        }

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
        if (Setting::getValue('shipping.enabled', '0') !== '1') {
            return false;
        }

        return $this->getActive() !== null;
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
                $config[$fieldKey] = $this->castBoolean($value, $default);
            } else {
                $config[$fieldKey] = $value;
            }
        }

        return $config;
    }

    public function storeProvider(string $gatewayKey): void
    {
        Setting::updateOrCreate(
            ['key' => 'shipping.provider'],
            ['value' => $gatewayKey]
        );
    }

    public function storeEnabled(bool $enabled): void
    {
        Setting::updateOrCreate(
            ['key' => 'shipping.enabled'],
            ['value' => $enabled ? '1' : '0']
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
            $value = Arr::get($values, $fieldKey, $field['default'] ?? null);

            if ($this->isBooleanField($field)) {
                $value = $value ? '1' : '0';
            }

            Setting::updateOrCreate(
                ['key' => $this->configKey($gatewayKey, $fieldKey)],
                ['value' => $value ?? '']
            );
        }
    }

    protected function configKey(string $gatewayKey, string $fieldKey): string
    {
        return "shipping.{$gatewayKey}.{$fieldKey}";
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function isBooleanField(array $field): bool
    {
        $type = $field['type'] ?? 'text';

        return in_array($type, ['toggle', 'boolean', 'checkbox'], true);
    }

    protected function castBoolean($value, $default = null): bool
    {
        if ($value === null) {
            return (bool) $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array((string) $value, ['1', 'true', 'on'], true);
    }
}
