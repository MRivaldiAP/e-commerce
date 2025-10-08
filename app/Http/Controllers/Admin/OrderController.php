<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Shipping\Exceptions\ShippingException;
use App\Services\Shipping\ShippingGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
}
