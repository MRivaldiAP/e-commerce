<?php

namespace App\Support;

use App\Models\Cart as CartModel;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
                'quantity' => $item->quantity,
                'image' => optional($product->images()->first())->path,
                'weight' => $weight,
            ];
        }

        $items = self::items();

        if (isset($items[$productId])) {
            $items[$productId]['quantity'] += $quantity;
        } else {
            $items[$productId] = [
                'product_id' => $productId,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
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
        return array_sum(array_map(function ($item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            return $price * $quantity;
        }, self::items()));
    }

    public static function summary(): array
    {
        $items = collect(self::items())
            ->map(function ($item) {
                $item['price'] = (float) ($item['price'] ?? 0);
                $item['quantity'] = (int) ($item['quantity'] ?? 0);
                $item['weight'] = (float) ($item['weight'] ?? config('shipping.default_weight', 1));
                $item['subtotal'] = $item['price'] * $item['quantity'];
                $item['price_formatted'] = number_format($item['price'], 0, ',', '.');
                $item['subtotal_formatted'] = number_format($item['subtotal'], 0, ',', '.');
                $item['image_url'] = ! empty($item['image']) ? asset('storage/' . $item['image']) : 'https://via.placeholder.com/120x120?text=No+Image';
                $item['product_url'] = route('products.show', $item['product_id']);

                return $item;
            })
            ->values()
            ->toArray();

        $totalPrice = self::totalPrice();
        $totalWeightKg = collect($items)->sum(function ($item) {
            return ($item['weight'] ?? 0) * ($item['quantity'] ?? 0);
        });
        $totalWeightGrams = (int) round($totalWeightKg * 1000);

        return [
            'items' => $items,
            'total_quantity' => self::totalQuantity(),
            'total_price' => $totalPrice,
            'total_price_formatted' => number_format($totalPrice, 0, ',', '.'),
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

