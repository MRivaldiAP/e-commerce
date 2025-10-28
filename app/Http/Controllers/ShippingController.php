<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\PageSetting;
use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\ShippingGatewayManager;
use App\Support\Cart;
use Illuminate\Support\Arr;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(Request $request, ShippingGatewayManager $shipping): View|RedirectResponse
    {
        if (Setting::getValue('shipping.enabled', '0') !== '1') {
            return redirect()->route('checkout.payment');
        }

        $gateway = $shipping->getActive();
        if (! $gateway) {
            return redirect()->route('checkout.payment')->with('error', 'Gateway pengiriman belum dikonfigurasi.');
        }

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $viewPath = base_path("themes/{$theme}/views/shipping.blade.php");

        if (! File::exists($viewPath)) {
            abort(404);
        }

        $pageSettings = PageSetting::forPage('shipping');
        $shippingData = $request->session()->get('checkout.shipping', []);
        $shippingCost = (int) Arr::get($shippingData, 'selected_rate.cost', Arr::get($shippingData, 'rate.cost', 0));
        $totals = [
            'subtotal' => $cartSummary['total_price'],
            'subtotal_formatted' => $cartSummary['total_price_formatted'],
            'shipping_cost' => $shippingCost,
            'shipping_cost_formatted' => number_format($shippingCost, 0, ',', '.'),
            'grand_total' => $cartSummary['total_price'] + $shippingCost,
            'grand_total_formatted' => number_format($cartSummary['total_price'] + $shippingCost, 0, ',', '.'),
        ];

        $provinces = Province::query()->orderBy('name')->get(['code', 'name']);

        return view()->file($viewPath, [
            'theme' => $theme,
            'settings' => $pageSettings,
            'cartSummary' => $cartSummary,
            'provinces' => $provinces,
            'shippingData' => $shippingData,
            'gatewayKey' => $gateway->key(),
            'gatewayLabel' => $gateway->label(),
            'gatewayDescription' => $gateway->description(),
            'shippingConfig' => $shipping->getGatewayConfig($gateway->key()),
            'checkoutTotals' => $totals,
        ]);
    }

    public function quote(Request $request, ShippingGatewayManager $shipping): JsonResponse
    {
        $gateway = $shipping->getActive();
        if (! $gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gateway pengiriman belum dikonfigurasi.',
            ], 422);
        }

        $data = $request->validate([
            'province_code' => ['required', 'string'],
            'regency_code' => ['required', 'string'],
            'district_code' => ['required', 'string'],
            'village_code' => ['required', 'string'],
            'postal_code' => ['required', 'string'],
            'couriers' => ['nullable', 'array'],
            'couriers.*' => ['string'],
        ]);

        $province = Province::find($data['province_code']);
        $regency = Regency::find($data['regency_code']);
        $district = District::find($data['district_code']);
        $village = Village::find($data['village_code']);

        if (! $province || ! $regency || ! $district || ! $village) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi pengiriman tidak valid.',
            ], 422);
        }

        $config = $shipping->getGatewayConfig($gateway->key());
        $cartSummary = Cart::summary();

        $destinationCodes = [
            'province' => $province->code,
            'regency' => $regency->code,
            'district' => $district->code,
            'village' => $village->code,
        ];

        try {
            $result = $gateway->checkRates([
                'config' => $config,
                'destination' => [
                    'province' => $province->name,
                    'regency' => $regency->name,
                    'district' => $district->name,
                    'village' => $village->name,
                    'postal_code' => $data['postal_code'],
                    'codes' => $destinationCodes,
                ],
                'destination_codes' => $destinationCodes,
                'weight' => $cartSummary['total_weight_grams'] ?? 0,
                'subtotal' => $cartSummary['total_price'] ?? 0,
                'couriers' => $data['couriers'] ?? null,
            ]);
        } catch (ShippingException $exception) {
            Log::warning('Gagal mengambil ongkir Biteship', [
                'message' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $result,
        ]);
    }

    public function store(Request $request, ShippingGatewayManager $shipping): RedirectResponse|JsonResponse
    {
        $gateway = $shipping->getActive();
        if (! $gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gateway pengiriman belum dikonfigurasi.',
            ], 422);
        }

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keranjang Anda kosong.',
            ], 422);
        }

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:500'],
            'province_code' => ['required', 'string'],
            'regency_code' => ['required', 'string'],
            'district_code' => ['required', 'string'],
            'village_code' => ['required', 'string'],
            'postal_code' => ['required', 'string'],
            'selected.courier' => ['required', 'string'],
            'selected.service' => ['required', 'string'],
            'selected.description' => ['nullable', 'string'],
            'selected.cost' => ['required', 'numeric', 'min:0'],
            'selected.etd' => ['nullable', 'string'],
            'selected.courier_name' => ['nullable', 'string'],
        ]);

        $province = Province::find($data['province_code']);
        $regency = Regency::find($data['regency_code']);
        $district = District::find($data['district_code']);
        $village = Village::find($data['village_code']);

        if (! $province || ! $regency || ! $district || ! $village) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi pengiriman tidak valid.',
            ], 422);
        }

        $rate = [
            'courier' => strtolower($data['selected']['courier']),
            'courier_name' => $data['selected']['courier_name'] ?? $data['selected']['courier'],
            'service' => $data['selected']['service'],
            'description' => $data['selected']['description'] ?? '',
            'cost' => (int) $data['selected']['cost'],
            'etd' => $data['selected']['etd'] ?? '',
            'currency' => 'IDR',
        ];

        $shippingData = [
            'provider' => $gateway->key(),
            'contact' => [
                'name' => $data['recipient_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ],
            'address' => [
                'street' => $data['address'],
                'province_code' => $province->code,
                'province_name' => $province->name,
                'regency_code' => $regency->code,
                'regency_name' => $regency->name,
                'district_code' => $district->code,
                'district_name' => $district->name,
                'village_code' => $village->code,
                'village_name' => $village->name,
                'postal_code' => $data['postal_code'],
            ],
            'selected_rate' => $rate,
            'selection' => [
                'courier' => $rate['courier'],
                'service' => $rate['service'],
                'description' => $rate['description'],
                'etd' => $rate['etd'],
            ],
            'cost' => $rate['cost'],
            'weight_grams' => $cartSummary['total_weight_grams'] ?? 0,
            'items' => $cartSummary['items'] ?? [],
        ];

        $request->session()->put('checkout.shipping', $shippingData);

        return response()->json([
            'status' => 'ok',
            'redirect' => route('checkout.payment'),
        ]);
    }

    public function track(Request $request, ShippingGatewayManager $shipping): JsonResponse
    {
        $gateway = $shipping->getActive();
        if (! $gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gateway pengiriman belum dikonfigurasi.',
            ], 422);
        }

        $data = $request->validate([
            'tracking_number' => ['required', 'string'],
            'courier' => ['nullable', 'string'],
        ]);

        $config = $shipping->getGatewayConfig($gateway->key());

        try {
            $result = $gateway->track($data['tracking_number'], [
                'courier' => $data['courier'] ?? null,
                'config' => $config,
            ]);
        } catch (ShippingException $exception) {
            Log::warning('Gagal melacak pengiriman Biteship', [
                'message' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $result,
        ]);
    }
}
