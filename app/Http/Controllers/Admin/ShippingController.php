<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Shipping\ShippingGateway;
use App\Services\Shipping\ShippingGatewayManager;
use Creasi\Nusa\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(Request $request, ShippingGatewayManager $shipping): View
    {
        $gateways = $shipping->all();
        $configs = $shipping->getAllGatewayConfigs();
        $activeKey = $shipping->getActiveKey();
        $enabled = Setting::getValue('shipping.enabled', '0') === '1';
        $provinces = Province::query()->orderBy('name')->get(['code', 'name']);

        return view('admin.shipping.index', [
            'gateways' => $gateways,
            'configs' => $configs,
            'activeKey' => $activeKey,
            'enabled' => $enabled,
            'provinces' => $provinces,
        ]);
    }

    public function update(Request $request, ShippingGatewayManager $shipping): RedirectResponse
    {
        $gateways = $shipping->all();
        $gatewayKeys = array_keys($gateways);

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'provider' => ['nullable', Rule::in($gatewayKeys)],
        ]);

        $enabled = (bool) ($validated['enabled'] ?? false);
        $selectedProvider = $validated['provider'] ?? null;

        if ($enabled && empty($selectedProvider)) {
            return back()->withErrors(['provider' => 'Pilih gateway pengiriman untuk mengaktifkan layanan.']);
        }

        if ($enabled && $selectedProvider) {
            /** @var ShippingGateway $gateway */
            $gateway = $gateways[$selectedProvider];
            $currentConfig = $shipping->getGatewayConfig($selectedProvider);

            $configRules = [];
            foreach ($gateway->configFields() as $field) {
                $fieldKey = $field['key'];
                $rule = $field['rules'] ?? 'nullable';
                $hasStoredValue = array_key_exists($fieldKey, $currentConfig) && $currentConfig[$fieldKey];
                if ($hasStoredValue) {
                    $rule = str_replace('required', 'nullable', $rule);
                }
                $configRules['config.' . $fieldKey] = $rule;
            }

            $validated = $request->validate(array_merge([
                'enabled' => ['nullable', 'boolean'],
                'provider' => ['nullable', Rule::in($gatewayKeys)],
            ], $configRules));

            $configValues = [];
            foreach ($gateway->configFields() as $field) {
                $fieldKey = $field['key'];
                $type = $field['type'] ?? 'text';
                if (in_array($type, ['toggle', 'boolean', 'checkbox'], true)) {
                    $configValues[$fieldKey] = $request->boolean('config.' . $fieldKey);
                } else {
                    $configValues[$fieldKey] = $validated['config'][$fieldKey] ?? ($currentConfig[$fieldKey] ?? null);
                }
            }

            $shipping->storeProvider($selectedProvider);
            $shipping->storeConfig($selectedProvider, $configValues);
        }

        $shipping->storeEnabled($enabled);

        if (! $enabled) {
            $shipping->storeProvider('');
        }

        return redirect()->route('admin.shipping.index')->with('success', 'Pengaturan pengiriman diperbarui.');
    }
}
