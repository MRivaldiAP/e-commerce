<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request, PaymentGatewayManager $payments): View
    {
        $gateways = $payments->all();
        $activeKey = $payments->getActiveKey();
        $configs = $payments->getAllGatewayConfigs();

        $methodSelections = [];
        foreach ($gateways as $key => $gateway) {
            $selected = $payments->getSelectedMethodKeys($key);
            if (empty($selected)) {
                $selected = $payments->getDefaultMethodKeys($gateway);
            }
            $methodSelections[$key] = $selected;
        }

        $defaultGateway = $activeKey ?? array_key_first($gateways);

        return view('admin.payments.index', [
            'gateways' => $gateways,
            'activeGatewayKey' => $activeKey,
            'configs' => $configs,
            'methodSelections' => $methodSelections,
            'defaultGatewayKey' => $defaultGateway,
        ]);
    }

    public function update(Request $request, PaymentGatewayManager $payments): RedirectResponse
    {
        $gateways = $payments->all();
        $gatewayKeys = array_keys($gateways);

        if (empty($gatewayKeys)) {
            return back()->withErrors(['gateway' => 'Tidak ada gateway pembayaran yang tersedia.']);
        }

        $baseRules = [
            'gateway' => ['required', Rule::in($gatewayKeys)],
        ];

        $validated = $request->validate($baseRules);

        $selectedGatewayKey = $validated['gateway'];
        $gateway = $gateways[$selectedGatewayKey];

        $methodKeys = array_keys($gateway->availableMethods());
        $currentConfig = $payments->getGatewayConfig($selectedGatewayKey);
        $methodRules = [
            'methods' => ['required', 'array', 'min:1'],
            'methods.*' => ['string', Rule::in($methodKeys)],
        ];

        $configRules = [];
        foreach ($gateway->configFields() as $field) {
            $rule = $field['rules'] ?? 'nullable';
            $fieldKey = $field['key'];
            $hasStoredValue = array_key_exists($fieldKey, $currentConfig) && $currentConfig[$fieldKey] !== null && $currentConfig[$fieldKey] !== '';
            if ($hasStoredValue) {
                $rule = str_replace('required', 'nullable', $rule);
            }
            $configRules['config.' . $fieldKey] = $rule;
        }

        $validated = $request->validate(array_merge($baseRules, $methodRules, $configRules));

        $methods = $validated['methods'];
        if (empty($methods)) {
            $methods = $payments->getDefaultMethodKeys($gateway);
        }

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

        $payments->storeGateway($selectedGatewayKey);
        $payments->storeMethods($selectedGatewayKey, $methods);
        $payments->storeConfig($selectedGatewayKey, $configValues);

        return redirect()->route('admin.payments.index')->with('success', 'Pengaturan pembayaran diperbarui.');
    }
}
