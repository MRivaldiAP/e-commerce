<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
        // $this->middleware(['auth', 'can:manage-products']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['keyword','category_id','min_price','max_price','status']);
        $perPage = (int) $request->get('per_page', 15);
        $products = $this->service->getAllProducts($filters, $perPage);

        return view('admin.products.index', compact('products', 'filters'));
    }

    public function create()
    {
        $categories = $this->service->getAllCategories();
        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        try {
            $files = $request->file('images', []);
            $product = $this->service->createProduct($data);

            if (!empty($files)) {
                $this->service->addImages($product->id, $files);
            }

            return redirect()->route('admin.products.edit', $product->id)
                ->with('success', 'Product created successfully.');
        } catch (\Throwable $e) {
            Log::error('Create product failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Create product failed.');
        }
    }

    public function edit($id)
    {
        $product = $this->service->getProductById($id);
        if (! $product) {
            return redirect()->route('admin.products.index')->with('error', 'Product not found.');
        }

        $categories = $this->service->getAllCategories();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $files = $request->file('images', []);
            $product = $this->service->updateProduct($id, $data);

            if (!empty($files)) {
                $this->service->addImages($product->id, $files);
            }

            return redirect()->route('admin.products.edit', $product->id)
                ->with('success', 'Product updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Update product failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Update product failed.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteProduct($id);
            return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
        } catch (\Throwable $e) {
            Log::error('Delete product failed: '.$e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'Delete product failed.');
        }
    }

    public function uploadImages(Request $request, $id)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $files = $request->file('images');
            $this->service->addImages($id, $files);
            return back()->with('success', 'Images uploaded.');
        } catch (\Throwable $e) {
            Log::error('Upload images failed: '.$e->getMessage());
            return back()->with('error', 'Upload images failed.');
        }
    }

    public function removeImage($productId, $imageId)
    {
        try {
            $this->service->removeImage($productId, $imageId);
            return back()->with('success', 'Image removed.');
        } catch (\Throwable $e) {
            Log::error('Remove image failed: '.$e->getMessage());
            return back()->with('error', 'Remove image failed.');
        }
    }

    public function updateStock(Request $request, $id)
    {
        $data = $request->validate([
            'quantity' => 'required|integer',
            'mode' => 'nullable|in:set,increase,decrease',
        ]);

        try {
            $this->service->updateStock($id, (int) $data['quantity'], $data['mode'] ?? 'set');
            return back()->with('success', 'Stock updated.');
        } catch (\Throwable $e) {
            Log::error('Update stock failed: '.$e->getMessage());
            return back()->with('error', 'Update stock failed.');
        }
    }

    public function syncCategories(Request $request, $id)
    {
        $data = $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        try {
            $this->service->syncCategories($id, $data['category_ids'] ?? []);
            return back()->with('success', 'Categories synced.');
        } catch (\Throwable $e) {
            Log::error('Sync categories failed: '.$e->getMessage());
            return back()->with('error', 'Sync categories failed.');
        }
    }

    public function show($id)
    {
        $product = $this->service->getProductById($id);
        if (! $product) {
            return redirect()->route('admin.products.index')->with('error', 'Product not found.');
        }

        return view('admin.products.show', compact('product'));
    }

    public function bulk(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!empty($ids)) {
            Product::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

}
