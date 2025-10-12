<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Shipping\RajaOngkirLocationImporter;
use App\Services\Shipping\RajaOngkirShippingGateway;
use App\Services\Shipping\ShippingGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ShippingController extends Controller
{
    public function index(Request $request, ShippingGatewayManager $shipping): View
    {
        $gateways = $shipping->all();
        $activeGatewayKey = $shipping->getActiveKey();
        $configs = $shipping->getAllGatewayConfigs();
        $shippingEnabled = Setting::getValue('shipping.enabled', '0') === '1';
        $defaultGatewayKey = $activeGatewayKey ?? array_key_first($gateways);

        return view('admin.shipping.index', [
            'gateways' => $gateways,
            'activeGatewayKey' => $activeGatewayKey,
            'configs' => $configs,
            'shippingEnabled' => $shippingEnabled,
            'defaultGatewayKey' => $defaultGatewayKey,
        ]);
    }

    public function update(Request $request, ShippingGatewayManager $shipping, RajaOngkirLocationImporter $locationImporter): RedirectResponse
    {
        $gateways = $shipping->all();
        $gatewayKeys = array_keys($gateways);
        $enabled = $request->boolean('shipping_enabled');

        Setting::updateOrCreate(
            ['key' => 'shipping.enabled'],
            ['value' => $enabled ? '1' : '0']
        );

        if (! $enabled) {
            return redirect()->route('admin.shipping.index')->with('success', 'Pengiriman dinonaktifkan.');
        }

        if (empty($gatewayKeys)) {
            return redirect()->route('admin.shipping.index')->withErrors([
                'gateway' => 'Tidak ada gateway pengiriman yang tersedia.',
            ]);
        }

        $baseRules = [
            'gateway' => ['required', Rule::in($gatewayKeys)],
        ];

        $validated = $request->validate($baseRules);

        $selectedGatewayKey = $validated['gateway'];
        $gateway = $gateways[$selectedGatewayKey];
        $currentConfig = $shipping->getGatewayConfig($selectedGatewayKey);

        $configRules = [];
        foreach ($gateway->configFields() as $field) {
            $fieldKey = $field['key'];
            $rule = $field['rules'] ?? 'nullable';
            $hasStoredValue = array_key_exists($fieldKey, $currentConfig)
                && $currentConfig[$fieldKey] !== null
                && $currentConfig[$fieldKey] !== ''
                && $currentConfig[$fieldKey] !== [];

            if ($hasStoredValue) {
                $rule = str_replace('required', 'nullable', $rule);
            }

            if (($field['multiple'] ?? false) === true) {
                $configRules['config.'.$fieldKey] = $rule;
                $configRules['config.'.$fieldKey.'.*'] = 'string';
            } else {
                $configRules['config.'.$fieldKey] = $rule;
            }
        }

        $request->validate($configRules);

        $configValues = [];
        foreach ($gateway->configFields() as $field) {
            $fieldKey = $field['key'];
            $type = $field['type'] ?? 'text';

            if (in_array($type, ['toggle', 'boolean', 'checkbox'], true)) {
                $configValues[$fieldKey] = $request->boolean('config.'.$fieldKey);
            } elseif (($field['multiple'] ?? false) === true || in_array($type, ['array', 'list', 'multiselect'], true)) {
                $configValues[$fieldKey] = array_values((array) $request->input('config.'.$fieldKey, $currentConfig[$fieldKey] ?? []));
            } else {
                $configValues[$fieldKey] = $request->input('config.'.$fieldKey, $currentConfig[$fieldKey] ?? null);
            }
        }

        $shipping->storeGateway($selectedGatewayKey);
        $shipping->storeConfig($selectedGatewayKey, $configValues);

        if ($gateway instanceof RajaOngkirShippingGateway) {
            $apiKey = (string) ($configValues['api_key'] ?? $currentConfig['api_key'] ?? '');
            $accountType = (string) ($configValues['account_type'] ?? $currentConfig['account_type'] ?? 'starter');

            if ($apiKey !== '') {
                try {
                    $locationImporter->sync($apiKey, $accountType);
                } catch (Throwable $exception) {
                    report($exception);

                    return redirect()->route('admin.shipping.index')->withErrors([
                        'gateway' => 'Pengaturan tersimpan, tetapi gagal memperbarui data lokasi RajaOngkir: '.$exception->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.shipping.index')->with('success', 'Pengaturan pengiriman diperbarui.');
    }
}
