<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\PageSetting;
use App\Services\Shipping\ShippingGatewayManager;
use App\Support\Cart;
use App\Support\ShippingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CartController extends Controller
{
    public function index(ShippingGatewayManager $shipping)
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = PageSetting::forPage('cart');

        $viewPath = base_path("themes/{$theme}/views/cart.blade.php");
        if (! File::exists($viewPath)) {
            abort(404);
        }

        $cart = Cart::summary();
        $shippingEnabled = Setting::getValue('shipping.enabled', '0') === '1';
        $activeProvider = Setting::getValue('shipping.provider', '');

        return view()->file($viewPath, [
            'theme' => $theme,
            'settings' => $settings,
            'cartSummary' => $cart,
            'shippingEnabled' => $shippingEnabled && ! empty($activeProvider),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::with('images')->findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;

        Cart::add($product, $quantity);
        ShippingSession::clear();

        $summary = Cart::summary();

        return response()->json([
            'status' => 'ok',
            'message' => 'Produk ditambahkan ke keranjang.',
            'summary' => $summary,
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        Cart::updateQuantity($product->getKey(), (int) $data['quantity']);
        ShippingSession::clear();

        $summary = Cart::summary();

        return response()->json([
            'status' => 'ok',
            'summary' => $summary,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        Cart::remove($product->getKey());
        ShippingSession::clear();

        $summary = Cart::summary();

        return response()->json([
            'status' => 'ok',
            'summary' => $summary,
        ]);
    }
}

