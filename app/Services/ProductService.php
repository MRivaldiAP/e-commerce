<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()->with(['categories', 'images']);

        if (!empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('sku', 'like', "%{$kw}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $filters['category_id']));
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('id')->paginate($perPage);
    }

    public function getProductById(int $id): ?Product
    {
        return Product::with(['categories', 'images'])->find($id);
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids'], $data['images']);

            $product = Product::create($data);

            if (!empty($categoryIds)) {
                $product->categories()->sync($categoryIds);
            }

            return $product->load(['categories', 'images']);
        });
    }

    public function updateProduct(int $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);

            $categoryIds = $data['category_ids'] ?? null;
            unset($data['category_ids'], $data['images']);

            if (!empty($data)) {
                $product->update($data);
            }

            if (!is_null($categoryIds)) {
                $product->categories()->sync($categoryIds);
            }

            return $product->load(['categories', 'images']);
        });
    }

    public function deleteProduct(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $product = Product::with('images')->findOrFail($id);

            foreach ($product->images as $img) {
                if ($img->path) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }

            $product->categories()->detach();

            return (bool) $product->delete();
        });
    }

    public function updateStock(int $id, int $quantity, string $mode = 'set'): Product
    {
        $product = Product::findOrFail($id);

        if ($mode === 'increase') {
            $product->stock = $product->stock + $quantity;
        } elseif ($mode === 'decrease') {
            $product->stock = $product->stock - $quantity;
        } else {
            $product->stock = $quantity;
        }

        $product->save();
        return $product;
    }

    public function attachCategories(int $productId, array $categoryIds): Product
    {
        $product = Product::findOrFail($productId);
        $product->categories()->attach($categoryIds);
        return $product->load('categories');
    }

    public function detachCategories(int $productId, array $categoryIds): Product
    {
        $product = Product::findOrFail($productId);
        $product->categories()->detach($categoryIds);
        return $product->load('categories');
    }

    public function syncCategories(int $productId, array $categoryIds): Product
    {
        $product = Product::findOrFail($productId);
        $product->categories()->sync($categoryIds);
        return $product->load('categories');
    }

    public function searchProducts(string $keyword, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['keyword'] = $keyword;
        return $this->getAllProducts($filters, $perPage);
    }

    public function addImages(int $productId, array $files): Collection
    {
        $product = Product::findOrFail($productId);

        $created = new Collection();
        foreach ($files as $file) {
            $path = $file->store('products', ['disk' => 'public']);
            $created->push(
                $product->images()->create(['path' => $path])
            );
        }

        return $created;
    }

    public function listImages(int $productId): Collection
    {
        $product = Product::with('images')->findOrFail($productId);
        return $product->images;
    }

    public function removeImage(int $productId, int $imageId): bool
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->whereKey($imageId)->firstOrFail();

        if ($image->path) {
            Storage::disk('public')->delete($image->path);
        }

        return (bool) $image->delete();
    }

    public function replaceImages(int $productId, array $files): Collection
    {
        return DB::transaction(function () use ($productId, $files) {
            $product = Product::with('images')->findOrFail($productId);

            foreach ($product->images as $img) {
                if ($img->path) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }

            return $this->addImages($productId, $files);
        });
    }

    public function getAllCategories(): Collection
    {
        return Category::withCount('products')->orderBy('name')->get();
    }

    public function getAllBrands(): Collection
    {
        return Brand::orderBy('name')->get();
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::with('products')->find($id);
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function deleteCategory(int $id): bool
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            throw new \RuntimeException('Kategori masih memiliki produk.');
        }

        return (bool) $category->delete();
    }

    public function getProductsByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::whereHas('categories', fn($q) => $q->where('categories.id', $categoryId))
            ->with(['categories', 'images'])
            ->paginate($perPage);
    }
}
