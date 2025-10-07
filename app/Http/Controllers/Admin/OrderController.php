<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\ShippingGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

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
        $shippingRecord = $order->shipping;
        if (! $shippingRecord) {
            return back()->with('error', 'Data pengiriman belum tersedia untuk pesanan ini.');
        }

        $data = $request->validate([
            'courier' => ['nullable', 'string', 'max:50'],
            'service' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'external_id' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'packing', 'in_transit', 'delivered', 'cancelled'])],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery' => ['nullable', 'date'],
        ]);

        $shippingRecord->forceFill([
            'courier' => $data['courier'] ?? $shippingRecord->courier,
            'service' => $data['service'] ?? $shippingRecord->service,
            'tracking_number' => $data['tracking_number'] ?? $shippingRecord->tracking_number,
            'external_id' => $data['external_id'] ?? $shippingRecord->external_id,
            'status' => $data['status'] ?? ($shippingRecord->status ?? 'pending'),
            'cost' => array_key_exists('cost', $data) ? (float) $data['cost'] : $shippingRecord->cost,
            'estimated_delivery' => $data['estimated_delivery'] ?? $shippingRecord->estimated_delivery,
        ]);

        $metadata = $shippingRecord->metadata ?? [];
        data_set($metadata, 'manual.updated_at', now()->toIso8601String());
        data_set($metadata, 'manual.updated_by', $request->user()?->id);
        $shippingRecord->metadata = $metadata;

        $shippingRecord->save();

        return back()->with('success', 'Data pengiriman berhasil diperbarui.');
    }

    public function createShipping(Request $request, Order $order, ShippingGatewayManager $shipping): RedirectResponse
    {
        if (! $shipping->isEnabled()) {
            return back()->with('error', 'Gateway pengiriman tidak aktif.');
        }

        $shippingRecord = $order->shipping;
        if (! $shippingRecord) {
            return back()->with('error', 'Data pengiriman belum tersedia untuk pesanan ini.');
        }

        $gateway = $shipping->getActive();
        if (! $gateway || $shippingRecord->provider !== $gateway->key()) {
            return back()->with('error', 'Pesanan ini tidak menggunakan gateway pengiriman yang aktif.');
        }

        $config = $shipping->getGatewayConfig($gateway->key());

        try {
            $response = $gateway->createShipment($config, [
                'order' => $order->toArray(),
                'shipping' => $shippingRecord->toArray(),
                'contact' => Arr::get($shippingRecord->metadata, 'contact', []),
                'address' => Arr::get($shippingRecord->metadata, 'address', []),
                'selection' => Arr::get($shippingRecord->metadata, 'selection', []),
            ]);
        } catch (ShippingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $metadata = $shippingRecord->metadata ?? [];
        data_set($metadata, 'gateway.last_action', 'create');
        data_set($metadata, 'gateway.last_response', $response);
        data_set($metadata, 'gateway.last_synced_at', now()->toIso8601String());

        $shippingRecord->forceFill([
            'status' => $response['status'] ?? ($shippingRecord->status ?: 'packing'),
            'tracking_number' => $response['tracking_number'] ?? $shippingRecord->tracking_number,
            'external_id' => $response['external_id'] ?? $shippingRecord->external_id,
            'estimated_delivery' => $this->resolveEstimatedDelivery($response, $shippingRecord->estimated_delivery),
            'metadata' => $metadata,
        ])->save();

        return back()->with('success', $response['message'] ?? 'Permintaan pengiriman dibuat.');
    }

    public function trackShipping(Request $request, Order $order, ShippingGatewayManager $shipping): RedirectResponse
    {
        if (! $shipping->isEnabled()) {
            return back()->with('error', 'Gateway pengiriman tidak aktif.');
        }

        $shippingRecord = $order->shipping;
        if (! $shippingRecord) {
            return back()->with('error', 'Data pengiriman belum tersedia untuk pesanan ini.');
        }

        $data = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'courier' => ['nullable', 'string', 'max:50'],
        ]);

        $gateway = $shipping->getActive();
        if (! $gateway || $shippingRecord->provider !== $gateway->key()) {
            return back()->with('error', 'Pesanan ini tidak menggunakan gateway pengiriman yang aktif.');
        }

        $config = $shipping->getGatewayConfig($gateway->key());
        $trackingNumber = $data['tracking_number'] ?: $shippingRecord->tracking_number;
        $courier = $data['courier'] ?: $shippingRecord->courier;

        if (! $trackingNumber) {
            return back()->with('error', 'Nomor resi belum diatur.');
        }

        try {
            $response = $gateway->trackShipment($config, [
                'tracking_number' => $trackingNumber,
                'courier' => $courier,
                'awb' => $trackingNumber,
            ]);
        } catch (ShippingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $metadata = $shippingRecord->metadata ?? [];
        data_set($metadata, 'tracking.summary', $response['summary'] ?? []);
        data_set($metadata, 'tracking.details', $response['details'] ?? []);
        data_set($metadata, 'tracking.delivery_status', $response['delivery_status'] ?? []);
        data_set($metadata, 'tracking.manifest', $response['manifest'] ?? []);
        data_set($metadata, 'tracking.checked_at', now()->toIso8601String());
        data_set($metadata, 'gateway.last_action', 'track');
        data_set($metadata, 'gateway.last_response', $response);
        data_set($metadata, 'gateway.last_synced_at', now()->toIso8601String());

        $shippingRecord->forceFill([
            'tracking_number' => $trackingNumber,
            'courier' => $courier ?: $shippingRecord->courier,
            'status' => $response['status'] ?? ($shippingRecord->status ?? 'in_transit'),
            'metadata' => $metadata,
        ])->save();

        return back()->with('success', 'Status pengiriman berhasil diperbarui.');
    }

    public function cancelShipping(Request $request, Order $order, ShippingGatewayManager $shipping): RedirectResponse
    {
        if (! $shipping->isEnabled()) {
            return back()->with('error', 'Gateway pengiriman tidak aktif.');
        }

        $shippingRecord = $order->shipping;
        if (! $shippingRecord) {
            return back()->with('error', 'Data pengiriman belum tersedia untuk pesanan ini.');
        }

        $gateway = $shipping->getActive();
        if (! $gateway || $shippingRecord->provider !== $gateway->key()) {
            return back()->with('error', 'Pesanan ini tidak menggunakan gateway pengiriman yang aktif.');
        }

        $config = $shipping->getGatewayConfig($gateway->key());

        try {
            $response = $gateway->cancelShipment($config, [
                'order' => $order->toArray(),
                'shipping' => $shippingRecord->toArray(),
            ]);
        } catch (ShippingException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $metadata = $shippingRecord->metadata ?? [];
        data_set($metadata, 'gateway.last_action', 'cancel');
        data_set($metadata, 'gateway.last_response', $response);
        data_set($metadata, 'gateway.last_synced_at', now()->toIso8601String());

        $shippingRecord->forceFill([
            'status' => $response['status'] ?? 'cancelled',
            'metadata' => $metadata,
        ])->save();

        return back()->with('success', $response['message'] ?? 'Pengiriman dibatalkan.');
    }

    protected function resolveEstimatedDelivery(array $response, $current)
    {
        $etd = Arr::get($response, 'estimated_delivery');
        if (! $etd) {
            $etd = Arr::get($response, 'etd');
        }

        if (! $etd) {
            return $current;
        }

        try {
            return Carbon::parse($etd);
        } catch (\Throwable $exception) {
            return $current;
        }
    }
}
