<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

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
        $brands = $this->service->getAllBrands();
        return view('admin.products.create', compact('categories', 'brands'));
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

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil dibuat.');

        } catch (\Throwable $e) {
            Log::error('Create product failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat produk.');
        }
    }

    public function edit($id)
    {
        $product = $this->service->getProductById($id);
        if (! $product) {
            return redirect()->route('admin.products.index')->with('error', 'Produk tidak ditemukan.');
        }

        $categories = $this->service->getAllCategories();
        $brands = $this->service->getAllBrands();
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
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
                ->with('success', 'Produk berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Update product failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui produk.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteProduct($id);
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Delete product failed: '.$e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'Gagal menghapus produk.');
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
            return back()->with('success', 'Gambar berhasil diunggah.');
        } catch (\Throwable $e) {
            Log::error('Upload images failed: '.$e->getMessage());
            return back()->with('error', 'Gagal mengunggah gambar.');
        }
    }

    public function removeImage($productId, $imageId)
    {
        try {
            $this->service->removeImage($productId, $imageId);
            return back()->with('success', 'Gambar berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Remove image failed: '.$e->getMessage());
            return back()->with('error', 'Gagal menghapus gambar.');
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
            return back()->with('success', 'Stok berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Update stock failed: '.$e->getMessage());
            return back()->with('error', 'Gagal memperbarui stok.');
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
            return back()->with('success', 'Kategori berhasil disinkron.');
        } catch (\Throwable $e) {
            Log::error('Sync categories failed: '.$e->getMessage());
            return back()->with('error', 'Gagal menyinkronkan kategori.');
        }
    }

    public function show($id)
    {
        $product = $this->service->getProductById($id);
        if (! $product) {
            return redirect()->route('admin.products.index')->with('error', 'Produk tidak ditemukan.');
        }

        return view('admin.products.show', compact('product'));
    }

    public function bulk(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids)) {
            Product::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

}
