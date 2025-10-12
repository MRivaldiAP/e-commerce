<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\ShippingGatewayManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $shippingEnabled = Setting::getValue('shipping.enabled', '0') === '1';
        $paymentEnabled = Setting::getValue('payment.gateway');

        $orders = Order::with(['user', 'payment', 'shipping', 'items.product'])
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', [
            'orders' => $orders,
            'shippingEnabled' => $shippingEnabled,
            'paymentEnabled' => ! empty($paymentEnabled),
        ]);
    }

    public function toggleReview(Request $request, Order $order): RedirectResponse
    {
        if (Setting::getValue('shipping.enabled', '0') === '1') {
            return back()->with('error', 'Status pesanan dikendalikan oleh pengiriman.');
        }

        $order->forceFill([
            'is_reviewed' => ! $order->is_reviewed,
        ])->save();

        $message = $order->is_reviewed
            ? 'Pesanan ditandai sudah diterima.'
            : 'Pesanan ditandai belum diterima.';

        return redirect()->route('admin.orders.index')->with('success', $message);
    }

    public function updateShipping(Request $request, Order $order, ShippingGatewayManager $shipping): RedirectResponse
    {
        if (Setting::getValue('shipping.enabled', '0') !== '1') {
            return back()->with('error', 'Pengiriman belum diaktifkan.');
        }

        $statuses = ['packing', 'in_transit', 'delivered', 'cancelled'];

        $data = $request->validate([
            'courier' => ['required', 'string', 'max:100'],
            'service' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:190'],
            'status' => ['required', 'string', Rule::in($statuses)],
            'estimated_delivery' => ['nullable', 'date'],
        ]);

        $shippingRecord = $order->shipping()->firstOrNew([]);
        $previousStatus = $shippingRecord->status;

        $shippingRecord->fill([
            'courier' => $data['courier'],
            'service' => $data['service'] ?? $shippingRecord->service,
            'tracking_number' => $data['tracking_number'] ?? null,
            'status' => $data['status'],
            'estimated_delivery' => $data['estimated_delivery'] ?? null,
        ]);

        $meta = $shippingRecord->meta ?? [];
        $meta['updated_by'] = $request->user()?->getKey();
        $shippingRecord->meta = $meta;

        $shippingRecord->save();

        if ($previousStatus !== 'cancelled' && $data['status'] === 'cancelled') {
            $gateway = $shipping->getActive();
            if ($gateway) {
                $config = $shipping->getGatewayConfig($gateway->key());
                $remoteId = $shippingRecord->remote_id ?? ($shippingRecord->meta['remote_id'] ?? null);

                if ($remoteId) {
                    try {
                        $gateway->cancel($remoteId, ['config' => $config]);
                    } catch (ShippingException $exception) {
                        report($exception);
                    }
                }
            }
        }

        return redirect()->route('admin.orders.index')->with('success', 'Informasi pengiriman diperbarui.');
    }

    public function createShippingOrder(Request $request, Order $order, ShippingGatewayManager $shipping): RedirectResponse
    {
        if (Setting::getValue('shipping.enabled', '0') !== '1') {
            return back()->with('error', 'Pengiriman belum diaktifkan.');
        }

        $gateway = $shipping->getActive();
        if (! $gateway) {
            return back()->with('error', 'Gateway pengiriman belum dikonfigurasi.');
        }

        $shippingRecord = $order->shipping()->first();
        if (! $shippingRecord) {
            return back()->with('error', 'Data pengiriman untuk pesanan ini belum tersedia.');
        }

        if (! empty($shippingRecord->remote_id)) {
            return back()->with('success', 'Pesanan pengiriman sudah dibuat sebelumnya.');
        }

        $meta = $shippingRecord->meta ?? [];
        $contact = Arr::get($meta, 'contact', []);
        $address = Arr::get($meta, 'address', []);

        if (empty($contact) || empty($address)) {
            return back()->with('error', 'Informasi kontak atau alamat penerima belum lengkap.');
        }

        $rate = Arr::get($meta, 'selected_rate', Arr::get($meta, 'selection', []));
        $rateCourier = Arr::get($rate, 'courier', $shippingRecord->courier);
        $rateService = Arr::get($rate, 'service', $shippingRecord->service);

        if (! $rateCourier || ! $rateService) {
            return back()->with('error', 'Pilihan kurir atau layanan belum ditentukan.');
        }

        $order->loadMissing('items.product');
        $items = $order->items->map(function ($item) {
            $product = $item->product;

            return [
                'name' => $product->name ?? 'Produk',
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'weight' => (float) ($product->weight ?? config('shipping.default_weight', 1)),
            ];
        })->values()->all();

        if (empty($items)) {
            return back()->with('error', 'Tidak ada produk pada pesanan ini.');
        }

        $config = $shipping->getGatewayConfig($gateway->key());

        try {
            $result = $gateway->createOrder([
                'config' => $config,
                'rate' => array_merge($rate, [
                    'courier' => $rateCourier,
                    'service' => $rateService,
                ]),
                'contact' => $contact,
                'address' => $address,
                'items' => $items,
                'reference' => $order->order_number,
            ]);
        } catch (ShippingException $exception) {
            report($exception);

            return back()->with('error', $exception->getMessage() ?: 'Gagal membuat pesanan pengiriman.');
        }

        if (isset($result['config'])) {
            unset($result['config']);
        }

        $shippingRecord->forceFill([
            'provider' => $gateway->key(),
            'courier' => strtolower((string) ($result['courier'] ?? $rateCourier)),
            'service' => $result['service'] ?? $rateService,
            'tracking_number' => $result['tracking_number'] ?? $shippingRecord->tracking_number,
            'cost' => (int) ($result['cost'] ?? $shippingRecord->cost ?? 0),
            'status' => $shippingRecord->status === 'pending' ? 'packing' : ($shippingRecord->status ?? 'packing'),
            'remote_id' => $result['remote_id'] ?? $shippingRecord->remote_id,
            'estimated_delivery' => $this->resolveEstimatedDelivery($result['etd'] ?? null),
        ]);

        $meta['selected_rate'] = array_merge($rate, [
            'cost' => (int) ($result['cost'] ?? Arr::get($rate, 'cost', $shippingRecord->cost ?? 0)),
            'etd' => $result['etd'] ?? Arr::get($rate, 'etd'),
        ]);
        $meta['rate'] = $result;
        $meta['last_synced_at'] = Carbon::now()->toIso8601String();
        $shippingRecord->meta = $meta;
        $shippingRecord->save();

        return back()->with('success', 'Pesanan pengiriman berhasil dibuat.');
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
}
