<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Shipping\ShippingGatewayManager;
use App\Support\Cart;
use App\Support\ShippingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function payment(Request $request, PaymentGatewayManager $payments, ShippingGatewayManager $shipping): View|RedirectResponse
    {
        $gateway = $payments->getActive();
        if (! $gateway) {
            abort(404, 'Gateway pembayaran belum dikonfigurasi.');
        }

        $cartSummary = Cart::summary();
        if (empty($cartSummary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $shippingEnabled = Setting::getValue('shipping.enabled', '0') === '1';
        $shippingData = $shippingEnabled ? $request->session()->get('checkout.shipping') : null;

        if ($shippingEnabled && empty($shippingData)) {
            return redirect()->route('checkout.shipping')->with('error', 'Lengkapi informasi pengiriman terlebih dahulu.');
        }

        $totals = $this->buildCheckoutTotals($cartSummary, $shippingData);

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

        $shippingCost = (float) Arr::get($shippingData, 'cost', 0);
        $cartSummary['shipping_cost'] = $shippingCost;
        $cartSummary['shipping_cost_formatted'] = number_format($shippingCost, 0, ',', '.');
        $cartSummary['grand_total'] = $cartSummary['total_price'] + $shippingCost;
        $cartSummary['grand_total_formatted'] = number_format($cartSummary['grand_total'], 0, ',', '.');

        return view()->file($viewPath, [
            'theme' => $theme,
            'gatewayKey' => $gateway->key(),
            'gatewayLabel' => $gateway->label(),
            'gatewayDescription' => $gateway->description(),
            'cartSummary' => $cartSummary,
            'checkoutTotals' => $totals,
            'shippingData' => $shippingData,
            'shippingEnabled' => $shippingEnabled,
            'methods' => $methods,
            'checkoutData' => $checkoutData,
            'selectedMethod' => $methods[0]['key'] ?? null,
            'feedbackStatus' => $feedbackStatus,
            'shippingEnabled' => $shippingEnabled,
            'shippingData' => $shippingData,
        ]);
    }

    public function createPaymentSession(Request $request, PaymentGatewayManager $payments, ShippingGatewayManager $shipping): JsonResponse
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

        $shippingEnabled = $shipping->isEnabled();
        $shippingData = ShippingSession::get();

        if ($shippingEnabled && ! ShippingSession::isReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Informasi pengiriman belum lengkap.',
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

        $shippingEnabled = Setting::getValue('shipping.enabled', '0') === '1';
        $shippingData = $shippingEnabled ? $request->session()->get('checkout.shipping') : null;

        if ($shippingEnabled && empty($shippingData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Informasi pengiriman belum lengkap.',
            ], 422);
        }

        $cartSummary = Cart::summary();
        $totals = $this->buildCheckoutTotals($cartSummary, $shippingData);
        $shippingCost = (int) ($totals['shipping_cost'] ?? 0);
        $grandTotal = (float) ($totals['grand_total'] ?? $cartSummary['total_price']);

        $cart = [
            'items' => $cartSummary['items'],
            'subtotal' => $cartSummary['total_price'],
            'shipping_cost' => $shippingCost,
            'total_price' => $grandTotal,
        ];

        $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        $order = null;

        try {
            DB::beginTransaction();

            $user = $this->resolveCheckoutUser($request, $shippingData);
            $address = $this->resolveCheckoutAddress($user, $shippingData);

            $order = Order::create([
                'user_id' => $user->getKey(),
                'address_id' => $address->getKey(),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_price' => $grandTotal,
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
                'amount' => $grandTotal,
            ]);

            if ($shippingEnabled) {
                $order->shipping()->create([
                    'provider' => Arr::get($shippingData, 'provider', 'manual'),
                    'courier' => Arr::get($shippingData, 'selection.courier'),
                    'service' => Arr::get($shippingData, 'selection.service'),
                    'cost' => $shippingCost,
                    'status' => 'pending',
                    'metadata' => [
                        'selection' => Arr::get($shippingData, 'selection', []),
                        'contact' => Arr::get($shippingData, 'contact', []),
                        'address' => Arr::get($shippingData, 'address', []),
                    ],
                ]);
            }

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

            if ($shippingEnabled && $shippingData) {
                $context['shipping'] = [
                    'courier' => Arr::get($shippingData, 'selected_rate.courier'),
                    'service' => Arr::get($shippingData, 'selected_rate.service'),
                    'cost' => $shippingCost,
                ];
            }

            $config = $payments->getGatewayConfig($gateway->key());
            $session = $gateway->createPaymentSession($config, $cart, $context);

            $reference = $session['reference'] ?? $orderNumber;
            $transactionId = $session['transaction_id'] ?? ($session['token'] ?? $reference);

            $paymentRecord->forceFill([
                'transaction_id' => $transactionId,
            ])->save();

            if ($shippingEnabled && $shippingData) {
                $order->shipping()->updateOrCreate([], [
                    'courier' => Arr::get($shippingData, 'selected_rate.courier', 'manual'),
                    'service' => Arr::get($shippingData, 'selected_rate.service'),
                    'tracking_number' => Arr::get($shippingData, 'selected_rate.tracking_number'),
                    'cost' => $shippingCost,
                    'status' => 'packing',
                    'estimated_delivery' => $this->resolveEstimatedDelivery(Arr::get($shippingData, 'selected_rate.etd')),
                    'remote_id' => Arr::get($shippingData, 'rate.remote_id'),
                    'meta' => $shippingData,
                ]);
            }

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

        if ($shippingEnabled) {
            $request->session()->forget('checkout.shipping');
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

    protected function resolveCheckoutUser(Request $request, ?array $shippingData = null): User
    {
        if ($request->user()) {
            return $request->user();
        }

        $email = Arr::get($shippingData, 'contact.email')
            ?: (string) $request->input('customer.email', 'guest@local.test');
        $name = Arr::get($shippingData, 'contact.name')
            ?: (string) $request->input('customer.first_name', 'Guest Customer');
        $phone = Arr::get($shippingData, 'contact.phone')
            ?: $request->input('customer.phone');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(12)),
                'phone' => $phone,
            ]
        );

        if ($phone && empty($user->phone)) {
            $user->forceFill(['phone' => $phone])->save();
        }

        return $user;
    }

    protected function resolveCheckoutAddress(User $user, ?array $shippingData = null): Address
    {
        if ($shippingData) {
            $address = $user->addresses()->firstOrNew(['is_default' => true]);
            $address->fill([
                'recipient_name' => Arr::get($shippingData, 'contact.name', $user->name ?? 'Pelanggan'),
                'phone' => Arr::get($shippingData, 'contact.phone', $user->phone ?? '0000000000'),
                'street' => Arr::get($shippingData, 'address.street', 'Alamat belum diatur'),
                'village' => Arr::get($shippingData, 'address.village_name', 'Belum diatur'),
                'subdistrict' => Arr::get($shippingData, 'address.district_name', 'Belum diatur'),
                'city' => Arr::get($shippingData, 'address.regency_name', 'Belum diatur'),
                'province' => Arr::get($shippingData, 'address.province_name', 'Belum diatur'),
                'postal_code' => Arr::get($shippingData, 'address.postal_code', '00000'),
                'is_default' => true,
            ]);
            $address->save();

            return $address;
        }

        $existing = $user->addresses()->first();
        if ($existing) {
            $existing->fill($payload)->save();

            return $existing;
        }

        return $user->addresses()->create($payload);
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

    /**
     * @param  array<string, mixed>  $cartSummary
     * @param  array<string, mixed>|null  $shippingData
     * @return array<string, mixed>
     */
    protected function buildCheckoutTotals(array $cartSummary, ?array $shippingData = null): array
    {
        $subtotal = (float) ($cartSummary['total_price'] ?? 0);
        $shippingCost = (int) Arr::get($shippingData, 'rate.cost', 0);
        $grandTotal = $subtotal + $shippingCost;

        return [
            'subtotal' => $subtotal,
            'subtotal_formatted' => number_format($subtotal, 0, ',', '.'),
            'shipping_cost' => $shippingCost,
            'shipping_cost_formatted' => number_format($shippingCost, 0, ',', '.'),
            'grand_total' => $grandTotal,
            'grand_total_formatted' => number_format($grandTotal, 0, ',', '.'),
        ];
    }

    protected function resolveEstimatedDelivery(?string $etd): ?Carbon
    {
        if (! $etd) {
            return null;
        }

        if (preg_match('/(\d+)/', $etd, $matches)) {
            $days = (int) $matches[1];
            if ($days > 0) {
                return Carbon::now()->addDays($days);
            }
        }

        return null;
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
