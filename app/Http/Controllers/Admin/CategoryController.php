<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
        // $this->middleware(['auth', 'can:manage-products']);
    }

    public function index()
    {
        $categories = $this->service->getAllCategories();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request)
    {
        try {
            $category = $this->service->createCategory($request->validated());
            return redirect()->route('admin.categories.index')->with('success', 'Category created.');
        } catch (\Throwable $e) {
            Log::error('Create category failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Create category failed.');
        }
    }

    public function edit($id)
    {
        $category = $this->service->getCategoryById($id);
        if (! $category) {
            return redirect()->route('admin.categories.index')->with('error', 'Category not found.');
        }

        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = $this->service->updateCategory($id, $request->validated());
            return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
        } catch (\Throwable $e) {
            Log::error('Update category failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Update category failed.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteCategory($id);
            return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
        } catch (\RuntimeException $re) {
            return back()->with('error', $re->getMessage());
        } catch (\Throwable $e) {
            Log::error('Delete category failed: '.$e->getMessage());
            return back()->with('error', 'Delete category failed.');
        }
    }

    public function products($id)
    {
        $page = $this->service->getProductsByCategory($id);
        return view('admin.categories.products', compact('page'));
    }
}
