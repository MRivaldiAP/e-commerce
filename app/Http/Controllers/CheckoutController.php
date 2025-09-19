<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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

        $feedbackStatus = $this->resolveStatusMessage($request->query('status'));

        return view()->file($viewPath, [
            'theme' => $theme,
            'gatewayKey' => $gateway->key(),
            'gatewayLabel' => $gateway->label(),
            'gatewayDescription' => $gateway->description(),
            'cartSummary' => $cartSummary,
            'methods' => $methods,
            'checkoutData' => $checkoutData,
            'selectedMethod' => $methods[0]['key'] ?? null,
            'feedbackStatus' => $feedbackStatus,
        ]);
    }

    public function createPaymentSession(Request $request, PaymentGatewayManager $payments): JsonResponse
    {
        $gateway = $payments->getActive();
        if (! $gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gateway pembayaran belum dikonfigurasi.',
            ], 422);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $cartItems = Cart::items();
        if (empty($cartItems)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keranjang Anda kosong.',
            ], 422);
        }

        $methods = $payments->getEnabledMethods($gateway->key());
        $selectedMethod = $payments->getEnabledMethod($data['payment_method'], $gateway->key());

        if (! $selectedMethod) {
            return response()->json([
                'status' => 'error',
                'message' => 'Metode pembayaran tidak tersedia.',
            ], 422);
        }

        $cart = [
            'items' => $cartItems,
            'total_price' => Cart::totalPrice(),
        ];

        $context = [
            'selected_method' => $data['payment_method'],
            'enabled_methods' => $methods,
            'success_url' => route('checkout.payment', ['status' => 'success']),
            'pending_url' => route('checkout.payment', ['status' => 'pending']),
            'error_url' => route('checkout.payment', ['status' => 'failed']),
            'cancel_url' => route('checkout.payment', ['status' => 'cancelled']),
            'notify_url' => route('checkout.payment.webhook', ['gateway' => $gateway->key()]),
        ];

        if ($request->user()) {
            $user = $request->user();
            $context['customer'] = array_filter([
                'first_name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
            ]);
        }

        try {
            $config = $payments->getGatewayConfig($gateway->key());
            $session = $gateway->createPaymentSession($config, $cart, $context);
        } catch (\Throwable $exception) {
            Log::warning('Gagal membuat sesi pembayaran', [
                'gateway' => $gateway->key(),
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'Gagal memproses pembayaran.',
            ], 422);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $session,
        ]);
    }

    public function webhook(Request $request, string $gateway): JsonResponse
    {
        Log::info('Notifikasi pembayaran diterima', [
            'gateway' => $gateway,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    protected function resolveStatusMessage(?string $status): ?array
    {
        if (! $status) {
            return null;
        }

        $messages = [
            'success' => ['message' => 'Pembayaran berhasil dikonfirmasi.', 'type' => 'success'],
            'pending' => ['message' => 'Pembayaran Anda masih menunggu konfirmasi dari penyedia layanan.', 'type' => 'info'],
            'failed' => ['message' => 'Pembayaran gagal diproses. Silakan coba kembali atau gunakan metode lain.', 'type' => 'error'],
            'cancelled' => ['message' => 'Anda membatalkan proses pembayaran.', 'type' => 'info'],
        ];

        return $messages[$status] ?? null;
    }
}
