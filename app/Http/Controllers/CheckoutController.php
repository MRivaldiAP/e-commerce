<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\PaymentGatewayManager;
use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        $cartSummary = Cart::summary();
        $cart = [
            'items' => $cartSummary['items'],
            'total_price' => $cartSummary['total_price'],
        ];

        $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        $order = null;

        try {
            DB::beginTransaction();

            $user = $this->resolveCheckoutUser($request);
            $address = $this->resolveCheckoutAddress($user);

            $order = Order::create([
                'user_id' => $user->getKey(),
                'address_id' => $address->getKey(),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_price' => $cartSummary['total_price'],
            ]);

            foreach ($cartSummary['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            $paymentRecord = $order->payment()->create([
                'method' => $this->determineStoredPaymentMethod($gateway->key(), $selectedMethod['key'] ?? $data['payment_method']),
                'status' => 'pending',
                'transaction_id' => $orderNumber,
                'amount' => $cartSummary['total_price'],
            ]);

            $context = [
                'selected_method' => $selectedMethod['key'] ?? $data['payment_method'],
                'enabled_methods' => $methods,
                'success_url' => route('orders.index', ['status' => 'success']),
                'pending_url' => route('checkout.payment', ['status' => 'pending']),
                'error_url' => route('checkout.payment', ['status' => 'failed']),
                'cancel_url' => route('checkout.payment', ['status' => 'cancelled']),
                'notify_url' => route('checkout.payment.webhook', ['gateway' => $gateway->key()]),
                'reference' => $orderNumber,
            ];

            $customerContext = $this->buildCustomerContext($user, $address);
            if (! empty($customerContext)) {
                $context['customer'] = $customerContext;
            }

            $config = $payments->getGatewayConfig($gateway->key());
            $session = $gateway->createPaymentSession($config, $cart, $context);

            $reference = $session['reference'] ?? $orderNumber;
            $transactionId = $session['transaction_id'] ?? ($session['token'] ?? $reference);

            $paymentRecord->forceFill([
                'transaction_id' => $transactionId,
            ])->save();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::warning('Gagal membuat sesi pembayaran', [
                'gateway' => $gateway->key(),
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'Gagal memproses pembayaran.',
            ], 422);
        }

        if ($order) {
            $this->rememberOrderInSession($request, $order);
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

        $reference = $this->extractOrderReference($request);
        $status = $this->extractPaymentStatus($request);

        if ($reference) {
            $payment = Payment::where('transaction_id', $reference)
                ->orWhereHas('order', fn ($query) => $query->where('order_number', $reference))
                ->first();

            if ($payment) {
                $this->updatePaymentStatus($payment, $status);
            } else {
                Log::warning('Pembayaran tidak ditemukan untuk notifikasi.', ['reference' => $reference]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function resolveCheckoutUser(Request $request): User
    {
        if ($request->user()) {
            return $request->user();
        }

        $email = (string) $request->input('customer.email', 'guest@local.test');
        $name = (string) $request->input('customer.first_name', 'Guest Customer');

        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(12)),
            ]
        );
    }

    protected function resolveCheckoutAddress(User $user): Address
    {
        $existing = $user->addresses()->first();
        if ($existing) {
            return $existing;
        }

        return $user->addresses()->create([
            'recipient_name' => $user->name ?? 'Pelanggan',
            'phone' => $user->phone ?? '0000000000',
            'street' => 'Alamat belum diatur',
            'village' => 'Belum diatur',
            'subdistrict' => 'Belum diatur',
            'city' => 'Belum diatur',
            'province' => 'Belum diatur',
            'postal_code' => '00000',
            'is_default' => true,
        ]);
    }

    protected function determineStoredPaymentMethod(string $gatewayKey, ?string $methodKey): string
    {
        $methodKey = strtolower($methodKey ?? '');

        if ($gatewayKey === 'midtrans') {
            return 'midtrans';
        }

        if (str_contains($methodKey, 'bank')) {
            return 'bank_transfer';
        }

        if (in_array($methodKey, ['va', 'virtual_account', 'cstore'], true)) {
            return 'bank_transfer';
        }

        return 'other';
    }

    protected function buildCustomerContext(User $user, Address $address): array
    {
        return array_filter([
            'first_name' => $user->name ?? null,
            'email' => $user->email ?? null,
            'phone' => $user->phone ?? null,
            'address' => $address->street ?? null,
        ]);
    }

    protected function rememberOrderInSession(Request $request, Order $order): void
    {
        $ids = collect($request->session()->get('orders.recent', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->push($order->getKey())
            ->unique()
            ->take(20)
            ->values()
            ->all();

        $request->session()->put('orders.recent', $ids);
    }

    protected function extractOrderReference(Request $request): ?string
    {
        $keys = [
            'order_id',
            'orderId',
            'reference',
            'reference_id',
            'referenceId',
            'transaction_id',
            'transactionId',
        ];

        foreach ($keys as $key) {
            if ($request->filled($key)) {
                return (string) $request->input($key);
            }
        }

        $data = $request->input('data');
        if (is_array($data)) {
            foreach ($keys as $key) {
                if (! empty($data[$key])) {
                    return (string) $data[$key];
                }
            }
        }

        return null;
    }

    protected function extractPaymentStatus(Request $request): ?string
    {
        $keys = [
            'transaction_status',
            'transactionStatus',
            'status',
            'payment_status',
            'paymentStatus',
            'status_code',
            'statusCode',
            'Status',
            'StatusCode',
            'fraud_status',
        ];

        $candidates = [];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $candidates[] = $request->input($key);
            }
        }

        $data = $request->input('data');
        if (is_array($data)) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data)) {
                    $candidates[] = $data[$key];
                }
            }
        }

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                continue;
            }

            $value = strtolower((string) $candidate);

            if (in_array($value, ['capture', 'settlement', 'success', 'paid', 'completed', 'sukses', 'berhasil', '200'], true)) {
                return 'success';
            }

            if (in_array($value, ['pending', 'challenge', 'waiting', 'in_process', 'process'], true)) {
                return 'pending';
            }

            if (in_array($value, ['deny', 'failed', 'failure', 'cancel', 'cancelled', 'expire', 'expired', 'error', '404'], true)) {
                return 'failed';
            }
        }

        return null;
    }

    protected function updatePaymentStatus(Payment $payment, ?string $status): void
    {
        if (! $status) {
            return;
        }

        $status = match ($status) {
            'success' => 'success',
            'failed' => 'failed',
            default => 'pending',
        };

        $attributes = ['status' => $status];
        if ($status === 'success') {
            $attributes['paid_at'] = now();
        } elseif ($status === 'failed') {
            $attributes['paid_at'] = null;
        }

        $payment->forceFill($attributes)->save();

        $order = $payment->order;
        if (! $order) {
            return;
        }

        if ($status === 'success' && $order->status !== 'paid') {
            $order->forceFill(['status' => 'paid'])->save();
        }

        if ($status === 'failed' && $order->status === 'pending') {
            $order->forceFill(['status' => 'cancelled'])->save();
        }
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
