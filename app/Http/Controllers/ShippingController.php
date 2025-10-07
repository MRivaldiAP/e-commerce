<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\ShippingGatewayManager;
use App\Support\Cart;
use App\Support\ShippingSession;
use Creasi\Nusa\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(Request $request, ShippingGatewayManager $shipping): View|RedirectResponse
    {
        if (! $shipping->isEnabled()) {
            return redirect()->route('checkout.payment');
        }

        $gateway = $shipping->getActive();
        if (! $gateway) {
            return redirect()->route('checkout.payment');
        }

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $gatewayKey = $gateway->key();
        $viewCandidates = [
            base_path("themes/{$theme}/views/shipping/{$gatewayKey}.blade.php"),
            base_path("themes/{$theme}/views/shipping/default.blade.php"),
            base_path("themes/{$theme}/views/shipping.blade.php"),
        ];

        $viewPath = collect($viewCandidates)->first(fn ($path) => File::exists($path));

        if (! $viewPath) {
            abort(404);
        }

        $provinces = Province::query()->orderBy('name')->get(['code', 'name'])->map(function ($province) {
            return [
                'code' => $province->code,
                'name' => $province->name,
            ];
        });

        return view()->file($viewPath, [
            'theme' => $theme,
            'gatewayKey' => $gatewayKey,
            'cartSummary' => $cartSummary,
            'provinces' => $provinces,
            'shippingData' => ShippingSession::get(),
        ]);
    }

    public function rates(Request $request, ShippingGatewayManager $shipping): JsonResponse
    {
        if (! $shipping->isEnabled()) {
            return response()->json(['status' => 'error', 'message' => 'Pengiriman tidak aktif.'], 404);
        }

        $data = $request->validate([
            'province_code' => ['required', 'string'],
            'regency_code' => ['required', 'string'],
            'district_code' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
        ]);

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return response()->json(['status' => 'error', 'message' => 'Keranjang kosong.'], 422);
        }

        $gateway = $shipping->getActive();
        if (! $gateway) {
            return response()->json(['status' => 'error', 'message' => 'Gateway pengiriman tidak tersedia.'], 404);
        }

        $config = $shipping->getGatewayConfig($gateway->key());
        $destinationCode = $data['district_code'] ?: $data['regency_code'];
        $destinationType = $data['district_code'] ? 'subdistrict' : 'city';

        try {
            $rates = $gateway->fetchRates($config, [
                'destination' => [
                    'code' => $destinationCode,
                    'type' => $destinationType,
                ],
                'weight' => max(1, (int) ($cartSummary['total_weight_grams'] ?? 1)),
            ]);
        } catch (ShippingException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'ok',
            'rates' => $rates,
        ]);
    }

    public function store(Request $request, ShippingGatewayManager $shipping): RedirectResponse
    {
        if (! $shipping->isEnabled()) {
            return redirect()->route('checkout.payment');
        }

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:500'],
            'province_code' => ['required', 'string', 'max:10'],
            'province_name' => ['required', 'string', 'max:255'],
            'regency_code' => ['required', 'string', 'max:10'],
            'regency_name' => ['required', 'string', 'max:255'],
            'district_code' => ['nullable', 'string', 'max:10'],
            'district_name' => ['nullable', 'string', 'max:255'],
            'village_code' => ['nullable', 'string', 'max:10'],
            'village_name' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'shipping_courier' => ['required', 'string', 'max:50'],
            'shipping_service' => ['required', 'string', 'max:100'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'shipping_description' => ['nullable', 'string', 'max:255'],
            'shipping_etd' => ['nullable', 'string', 'max:100'],
        ]);

        $gateway = $shipping->getActive();
        if (! $gateway) {
            return redirect()->route('checkout.payment')->with('error', 'Gateway pengiriman tidak tersedia.');
        }

        $config = $shipping->getGatewayConfig($gateway->key());
        $destinationCode = $data['district_code'] ?: $data['regency_code'];
        $destinationType = $data['district_code'] ? 'subdistrict' : 'city';

        try {
            $rates = $gateway->fetchRates($config, [
                'destination' => [
                    'code' => $destinationCode,
                    'type' => $destinationType,
                ],
                'weight' => max(1, (int) ($cartSummary['total_weight_grams'] ?? 1)),
            ]);
        } catch (ShippingException $exception) {
            return back()->withInput()->withErrors(['shipping_courier' => $exception->getMessage()]);
        }

        $selectedRate = collect($rates)->first(function ($rate) use ($data) {
            return strtolower(Arr::get($rate, 'courier')) === strtolower($data['shipping_courier'])
                && (string) Arr::get($rate, 'service') === (string) $data['shipping_service'];
        });

        if (! $selectedRate) {
            return back()->withInput()->withErrors(['shipping_service' => 'Layanan pengiriman yang dipilih tidak tersedia.']);
        }

        $cost = (float) Arr::get($selectedRate, 'cost', $data['shipping_cost']);
        $total = (float) $cartSummary['total_price'] + $cost;

        ShippingSession::store([
            'provider' => $gateway->key(),
            'contact' => [
                'name' => $data['contact_name'],
                'email' => $data['contact_email'],
                'phone' => $data['contact_phone'],
            ],
            'address' => [
                'street' => $data['address'],
                'province_code' => $data['province_code'],
                'province_name' => $data['province_name'],
                'regency_code' => $data['regency_code'],
                'regency_name' => $data['regency_name'],
                'district_code' => $data['district_code'],
                'district_name' => $data['district_name'],
                'village_code' => $data['village_code'],
                'village_name' => $data['village_name'],
                'postal_code' => $data['postal_code'],
                'recipient' => $data['contact_name'],
                'phone' => $data['contact_phone'],
            ],
            'selection' => [
                'courier' => Arr::get($selectedRate, 'courier'),
                'courier_name' => Arr::get($selectedRate, 'courier_name'),
                'service' => Arr::get($selectedRate, 'service'),
                'description' => Arr::get($selectedRate, 'description'),
                'etd' => Arr::get($selectedRate, 'etd'),
                'cost' => $cost,
            ],
            'cost' => $cost,
            'total' => $total,
            'metadata' => [
                'requested_at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('checkout.payment');
    }

    public function destroy(): RedirectResponse
    {
        ShippingSession::clear();

        return redirect()->route('checkout.shipping');
    }
}
