<?php

namespace App\Services\Payments;

use App\Models\Setting;
use Illuminate\Support\Arr;

class PaymentGatewayManager
{
    /**
     * @var array<string, \App\Services\Payments\PaymentGateway>
     */
    protected array $gateways = [];

    public function __construct()
    {
        $configured = config('payments.gateways', []);

        foreach ($configured as $class) {
            $instance = app($class);
            if ($instance instanceof PaymentGateway) {
                $this->gateways[$instance->key()] = $instance;
            }
        }
    }

    /**
     * @return array<string, PaymentGateway>
     */
    public function all(): array
    {
        return $this->gateways;
    }

    public function get(string $key): ?PaymentGateway
    {
        return $this->gateways[$key] ?? null;
    }

    public function getActiveKey(): ?string
    {
        $key = Setting::getValue('payment.gateway');

        return $key ?: null;
    }

    public function getActive(): ?PaymentGateway
    {
        $key = $this->getActiveKey();

        return $key ? $this->get($key) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEnabledMethods(?string $gatewayKey = null): array
    {
        $gateway = $this->resolveGateway($gatewayKey);
        if (! $gateway) {
            return [];
        }

        $selected = $this->getSelectedMethodKeys($gateway->key());
        $available = $gateway->availableMethods();

        $methods = [];
        foreach ($selected as $methodKey) {
            if (! isset($available[$methodKey])) {
                continue;
            }

            $method = $available[$methodKey];
            $method['key'] = $methodKey;
            $methods[] = $method;
        }

        return $methods;
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
            } else {
                $config[$fieldKey] = $value;
            }
        }

        return $config;
    }

    /**
     * @return array<string>
     */
    public function getSelectedMethodKeys(?string $gatewayKey = null): array
    {
        $gateway = $this->resolveGateway($gatewayKey);
        if (! $gateway) {
            return [];
        }

        $stored = Setting::getValue($this->methodsKey($gateway->key()), '[]');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        if (is_array($decoded) && ! empty($decoded)) {
            return array_values(array_filter($decoded, fn ($value) => is_string($value)));
        }

        return $this->getDefaultMethodKeys($gateway);
    }

    /**
     * @return array<string>
     */
    public function getDefaultMethodKeys(?PaymentGateway $gateway = null): array
    {
        $gateway ??= $this->getActive();
        if (! $gateway) {
            return [];
        }

        $methods = $gateway->availableMethods();
        if (empty($methods)) {
            return [];
        }

        $defaults = [];
        foreach ($methods as $key => $method) {
            if (Arr::get($method, 'default') === true) {
                $defaults[] = $key;
            }
        }

        if (! empty($defaults)) {
            return $defaults;
        }

        return array_keys($methods);
    }

    public function storeGateway(string $gatewayKey): void
    {
        Setting::updateOrCreate(
            ['key' => 'payment.gateway'],
            ['value' => $gatewayKey]
        );
    }

    public function storeMethods(string $gatewayKey, array $methods): void
    {
        Setting::updateOrCreate(
            ['key' => $this->methodsKey($gatewayKey)],
            ['value' => json_encode(array_values($methods))]
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
            }

            Setting::updateOrCreate(
                ['key' => $this->configKey($gatewayKey, $fieldKey)],
                ['value' => $value ?? '']
            );
        }
    }

    protected function resolveGateway(?string $gatewayKey = null): ?PaymentGateway
    {
        if ($gatewayKey) {
            return $this->get($gatewayKey);
        }

        return $this->getActive();
    }

    protected function configKey(string $gatewayKey, string $fieldKey): string
    {
        return "payment.{$gatewayKey}.{$fieldKey}";
    }

    protected function methodsKey(string $gatewayKey): string
    {
        return "payment.methods.{$gatewayKey}";
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function isBooleanField(array $field): bool
    {
        $type = $field['type'] ?? null;

        return in_array($type, ['boolean', 'toggle', 'checkbox'], true);
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
