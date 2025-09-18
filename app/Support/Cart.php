<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class Cart
{
    protected const SESSION_KEY = 'cart.items';

    public static function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    protected static function storeItems(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }

    public static function add(Product $product, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $items = self::items();
        $productId = (int) $product->getKey();

        if (isset($items[$productId])) {
            $items[$productId]['quantity'] += $quantity;
        } else {
            $items[$productId] = [
                'product_id' => $productId,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (int) $product->price,
                'quantity' => $quantity,
                'image' => optional($product->images()->first())->path,
            ];
        }

        self::storeItems($items);

        return $items[$productId];
    }

    public static function updateQuantity(int $productId, int $quantity): ?array
    {
        $items = self::items();
        $productId = (int) $productId;

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
        $items = self::items();
        unset($items[(int) $productId]);
        self::storeItems($items);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function totalQuantity(): int
    {
        return array_sum(Arr::pluck(self::items(), 'quantity'));
    }

    public static function totalPrice(): int
    {
        return array_sum(array_map(function ($item) {
            return (int) $item['price'] * (int) $item['quantity'];
        }, self::items()));
    }

    public static function summary(): array
    {
        $items = collect(self::items())
            ->map(function ($item) {
                $item['price'] = (int) $item['price'];
                $item['quantity'] = (int) $item['quantity'];
                $item['subtotal'] = $item['price'] * $item['quantity'];
                $item['price_formatted'] = number_format($item['price'], 0, ',', '.');
                $item['subtotal_formatted'] = number_format($item['subtotal'], 0, ',', '.');
                $item['image_url'] = $item['image'] ? asset('storage/' . $item['image']) : 'https://via.placeholder.com/120x120?text=No+Image';
                $item['product_url'] = route('products.show', $item['product_id']);

                return $item;
            })
            ->values()
            ->toArray();

        $totalPrice = self::totalPrice();

        return [
            'items' => $items,
            'total_quantity' => self::totalQuantity(),
            'total_price' => $totalPrice,
            'total_price_formatted' => number_format($totalPrice, 0, ',', '.'),
        ];
    }
}

