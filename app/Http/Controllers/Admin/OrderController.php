<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
