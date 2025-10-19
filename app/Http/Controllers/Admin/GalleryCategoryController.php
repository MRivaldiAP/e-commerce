<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryCategoryController extends Controller
{
    public function index(): View
    {
        $categories = GalleryCategory::orderBy('name')->get();

        return view('admin.gallery.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.gallery.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:gallery_categories,slug'],
        ]);

        $slug = $data['slug'] ?? null;
        if (! $slug) {
            $slug = Str::slug($data['name']);
        }

        GalleryCategory::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.gallery.categories.index')
            ->with('status', 'Kategori galeri berhasil disimpan.');
    }

    public function edit(GalleryCategory $category): View
    {
        return view('admin.gallery.categories.edit', compact('category'));
    }

    public function update(Request $request, GalleryCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:gallery_categories,slug,' . $category->id],
        ]);

        $slug = $data['slug'] ?? null;
        if (! $slug) {
            $slug = Str::slug($data['name']);
        }

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.gallery.categories.index')
            ->with('status', 'Kategori galeri diperbarui.');
    }

    public function destroy(GalleryCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.gallery.categories.index')
            ->with('status', 'Kategori galeri dihapus.');
    }
}
