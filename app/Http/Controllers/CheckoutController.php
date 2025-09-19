<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function payment(Request $request, PaymentGatewayManager $payments): View|RedirectResponse
    {
        $gateway = $payments->getActive();
        if (! $gateway) {
            abort(404, 'Gateway pembayaran belum dikonfigurasi.');
        }

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $viewPath = base_path("themes/{$theme}/views/payment.blade.php");
        if (! File::exists($viewPath)) {
            abort(404);
        }

        $methods = $payments->getEnabledMethods($gateway->key());
        if (empty($methods)) {
            $available = $gateway->availableMethods();
            foreach ($payments->getDefaultMethodKeys($gateway) as $methodKey) {
                if (isset($available[$methodKey])) {
                    $method = $available[$methodKey];
                    $method['key'] = $methodKey;
                    $methods[] = $method;
                }
            }
        }

        $config = $payments->getGatewayConfig($gateway->key());
        $checkoutData = $gateway->checkoutData($config, $methods, $cartSummary);

        return view()->file($viewPath, [
            'theme' => $theme,
            'gatewayKey' => $gateway->key(),
            'gatewayLabel' => $gateway->label(),
            'gatewayDescription' => $gateway->description(),
            'cartSummary' => $cartSummary,
            'methods' => $methods,
            'checkoutData' => $checkoutData,
            'selectedMethod' => $methods[0]['key'] ?? null,
        ]);
    }
}
