<?php

namespace App\Support;

use App\Models\Cart as CartModel;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class Cart
{
    protected const SESSION_KEY = 'cart.items';

    public static function items(): array
    {
        if (self::usesDatabase()) {
            return self::databaseItems();
        }

        return Session::get(self::SESSION_KEY, []);
    }

    protected static function usesDatabase(): bool
    {
        return Auth::check();
    }

    protected static function storeItems(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }

    public static function add(Product $product, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $productId = (int) $product->getKey();
        $weight = (float) ($product->weight ?? 0);

        $weight = (float) ($product->weight ?? config('shipping.default_weight', 1));

        if (self::usesDatabase()) {
            $cart = self::resolveUserCart();
            $item = $cart->items()->firstOrNew(['product_id' => $productId]);
            $item->quantity = ($item->exists ? (int) $item->quantity : 0) + $quantity;
            $item->price_snapshot = $product->price;
            $item->save();

            $items = self::items();

            return $items[$productId] ?? [
                'product_id' => $productId,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'base_price' => (float) $product->price,
                'quantity' => $item->quantity,
                'image' => optional($product->images()->first())->path,
                'weight' => $weight,
            ];
        }

        $items = self::items();

        if (isset($items[$productId])) {
            $items[$productId]['quantity'] += $quantity;
            if (! array_key_exists('base_price', $items[$productId])) {
                $items[$productId]['base_price'] = (float) $product->price;
            }
        } else {
            $items[$productId] = [
                'product_id' => $productId,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'base_price' => (float) $product->price,
                'quantity' => $quantity,
                'image' => optional($product->images()->first())->path,
                'weight' => $weight,
            ];
        }

        self::storeItems($items);

        if (isset($items[$productId]) && ! array_key_exists('weight', $items[$productId])) {
            $items[$productId]['weight'] = $weight;
            self::storeItems($items);
        }

        return $items[$productId];
    }

    public static function updateQuantity(int $productId, int $quantity): ?array
    {
        $productId = (int) $productId;

        if (self::usesDatabase()) {
            $cart = self::resolveUserCart();
            $item = $cart->items()->where('product_id', $productId)->first();

            if (! $item) {
                return null;
            }

            if ($quantity <= 0) {
                $item->delete();
                return null;
            }

            $item->quantity = $quantity;
            $item->save();

            $items = self::items();

            return $items[$productId] ?? null;
        }

        $items = self::items();

        if (! isset($items[$productId])) {
            return null;
        }

        if ($quantity <= 0) {
            unset($items[$productId]);
            self::storeItems($items);
            return null;
        }

        $items[$productId]['quantity'] = $quantity;
        self::storeItems($items);

        return $items[$productId];
    }

    public static function remove(int $productId): void
    {
        $productId = (int) $productId;

        if (self::usesDatabase()) {
            $cart = self::resolveUserCart();
            $cart->items()->where('product_id', $productId)->delete();
            return;
        }

        $items = self::items();
        unset($items[$productId]);
        self::storeItems($items);
    }

    public static function clear(): void
    {
        if (self::usesDatabase()) {
            self::resolveUserCart()->items()->delete();
            return;
        }

        Session::forget(self::SESSION_KEY);
    }

    public static function totalQuantity(): int
    {
        return array_sum(array_map(fn ($item) => (int) ($item['quantity'] ?? 0), self::items()));
    }

    public static function totalPrice(): float
    {
        return (float) (self::summary()['total_price'] ?? 0);
    }

    public static function summary(): array
    {
        $rawItems = collect(self::items());

        if ($rawItems->isEmpty()) {
            return [
                'items' => [],
                'total_quantity' => 0,
                'total_price' => 0.0,
                'total_price_formatted' => '0',
                'original_total' => 0.0,
                'original_total_formatted' => '0',
                'discount_total' => 0.0,
                'discount_total_formatted' => '0',
                'total_weight' => 0.0,
                'total_weight_formatted' => number_format(0, 2, ',', '.'),
                'total_weight_grams' => 1,
            ];
        }

        $productIds = $rawItems
            ->map(fn ($item, $key) => (int) ($item['product_id'] ?? $key))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $products = Product::with(['images', 'promotions'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy(fn (Product $product) => (int) $product->getKey());

        $totalPrice = 0.0;
        $originalTotal = 0.0;
        $discountTotal = 0.0;

        $items = $rawItems->map(function ($item, $key) use ($products, &$totalPrice, &$originalTotal, &$discountTotal) {
            $productId = (int) ($item['product_id'] ?? $key);
            $product = $products->get($productId);
            $basePrice = (float) ($item['base_price'] ?? $item['price'] ?? $product?->price ?? 0);
            $quantity = max(0, (int) ($item['quantity'] ?? 0));
            $weight = (float) ($item['weight'] ?? $product?->weight ?? config('shipping.default_weight', 1));
            $imagePath = $product?->images?->first()?->path ?? ($item['image'] ?? null);
            $imageUrl = $imagePath
                ? (Str::startsWith($imagePath, ['http://', 'https://'])
                    ? $imagePath
                    : asset('storage/' . ltrim($imagePath, '/')))
                : 'https://via.placeholder.com/120x120?text=No+Image';

            $promotion = $product?->currentPromotion();
            if ($promotion && ! $promotion->isActive()) {
                $promotion = null;
            }

            $finalPrice = $promotion ? $promotion->applyDiscount($basePrice) : $basePrice;
            $discountAmount = max(0, $basePrice - $finalPrice);
            $subtotal = $finalPrice * $quantity;
            $originalSubtotal = $basePrice * $quantity;

            $totalPrice += $subtotal;
            $originalTotal += $originalSubtotal;
            $discountTotal += $discountAmount * $quantity;

            return [
                'product_id' => $productId,
                'name' => $product?->name ?? ($item['name'] ?? 'Produk'),
                'slug' => $product?->slug ?? ($item['slug'] ?? ''),
                'price' => $finalPrice,
                'price_formatted' => number_format($finalPrice, 0, ',', '.'),
                'original_price' => $basePrice,
                'original_price_formatted' => number_format($basePrice, 0, ',', '.'),
                'quantity' => $quantity,
                'weight' => $weight,
                'subtotal' => $subtotal,
                'subtotal_formatted' => number_format($subtotal, 0, ',', '.'),
                'original_subtotal' => $originalSubtotal,
                'original_subtotal_formatted' => number_format($originalSubtotal, 0, ',', '.'),
                'discount_amount' => $discountAmount,
                'discount_amount_formatted' => number_format($discountAmount, 0, ',', '.'),
                'total_discount' => $discountAmount * $quantity,
                'total_discount_formatted' => number_format($discountAmount * $quantity, 0, ',', '.'),
                'has_promo' => $promotion && $discountAmount > 0,
                'promo_label' => $promotion?->label,
                'promo_type' => $promotion?->discount_type,
                'promo_expires_at' => optional($promotion?->ends_at)->toIso8601String(),
                'image' => $imagePath,
                'image_url' => $imageUrl,
                'product_url' => route('products.show', $productId),
            ];
        })->values()->toArray();

        $totalWeightKg = array_sum(array_map(function ($item) {
            return ($item['weight'] ?? 0) * ($item['quantity'] ?? 0);
        }, $items));
        $totalWeightGrams = (int) round($totalWeightKg * 1000);
        $totalQuantity = array_sum(array_map(fn ($item) => (int) ($item['quantity'] ?? 0), $items));

        return [
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'total_price' => $totalPrice,
            'total_price_formatted' => number_format($totalPrice, 0, ',', '.'),
            'original_total' => $originalTotal,
            'original_total_formatted' => number_format($originalTotal, 0, ',', '.'),
            'discount_total' => $discountTotal,
            'discount_total_formatted' => number_format($discountTotal, 0, ',', '.'),
            'total_weight' => $totalWeightKg,
            'total_weight_formatted' => number_format($totalWeightKg, 2, ',', '.'),
            'total_weight_grams' => max(1, $totalWeightGrams),
        ];
    }

    public static function syncWithUser(User $user): void
    {
        $sessionItems = Session::get(self::SESSION_KEY, []);

        if (empty($sessionItems)) {
            return;
        }

        $cart = $user->cart()->firstOrCreate([]);

        foreach ($sessionItems as $item) {
            if (empty($item['product_id'])) {
                continue;
            }

            $product = Product::find($item['product_id']);

            if (! $product) {
                continue;
            }

            $cartItem = $cart->items()->firstOrNew(['product_id' => $product->getKey()]);
            $cartItem->quantity = ($cartItem->exists ? (int) $cartItem->quantity : 0) + (int) ($item['quantity'] ?? 0);
            $cartItem->price_snapshot = $product->price;
            $cartItem->save();
        }

        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    protected static function databaseItems(): array
    {
        $cart = self::resolveUserCart();

        return $cart->items()
            ->with(['product.images'])
            ->get()
            ->mapWithKeys(function (CartItem $item) {
                $product = $item->product;
                $price = $item->price_snapshot ?? ($product?->price ?? 0);
                $imagePath = optional($product?->images?->first())->path;

                return [
                    $item->product_id => [
                        'product_id' => $item->product_id,
                        'name' => $product?->name ?? 'Produk',
                        'slug' => $product?->slug ?? '',
                        'price' => (float) $price,
                        'base_price' => (float) $price,
                        'quantity' => (int) $item->quantity,
                        'image' => $imagePath,
                        'weight' => (float) ($product?->weight ?? config('shipping.default_weight', 1)),
                    ],
                ];
            })
            ->toArray();
    }

    protected static function resolveUserCart(): CartModel
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->cart()->firstOrCreate([]);
    }
}

