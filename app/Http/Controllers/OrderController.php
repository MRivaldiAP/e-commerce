<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, PaymentGatewayManager $payments): View
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $viewPath = base_path("themes/{$theme}/views/order.blade.php");

        if (! File::exists($viewPath)) {
            Log::warning('Order view not found for theme.', ['theme' => $theme, 'path' => $viewPath]);
            abort(404);
        }

        $shippingEnabled = Setting::getValue('shipping.enabled', '0') === '1';
        $paymentEnabled = $payments->getActive() !== null;

        $orderIds = collect($request->session()->get('orders.recent', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $ordersQuery = Order::query()
            ->with([
                'items.product.images',
                'payment',
                'shipping',
            ])
            ->latest();

        $user = $request->user();

        if ($user) {
            $ordersQuery->where('user_id', $user->id);
            if ($orderIds->isNotEmpty()) {
                $ordersQuery->orWhereIn('id', $orderIds);
            }
        } elseif ($orderIds->isNotEmpty()) {
            $ordersQuery->whereIn('id', $orderIds);
        } else {
            $ordersQuery->whereRaw('1 = 0');
        }

        $orders = $ordersQuery->get();

        return view()->file($viewPath, [
            'theme' => $theme,
            'orders' => $orders,
            'shippingEnabled' => $shippingEnabled,
            'paymentEnabled' => $paymentEnabled,
            'feedbackStatus' => $this->resolveStatusMessage($request->query('status')),
        ]);
    }

    protected function resolveStatusMessage(?string $status): ?array
    {
        if (! $status) {
            return null;
        }

        $messages = [
            'success' => ['message' => 'Terima kasih! Pembayaran Anda sedang diverifikasi.', 'type' => 'success'],
            'pending' => ['message' => 'Pembayaran masih menunggu konfirmasi.', 'type' => 'info'],
            'failed' => ['message' => 'Pembayaran gagal diproses.', 'type' => 'error'],
            'cancelled' => ['message' => 'Anda membatalkan proses pembayaran.', 'type' => 'info'],
        ];

        return $messages[$status] ?? null;
    }
}
