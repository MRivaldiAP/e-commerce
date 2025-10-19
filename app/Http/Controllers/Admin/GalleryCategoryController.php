<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

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
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $this->generateSlug($data['name'], $data['slug'] ?? null);

        GalleryCategory::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.gallery.categories.index')
            ->with('status', 'Kategori galeri berhasil ditambahkan.');
    }

    public function edit(GalleryCategory $category): View
    {
        return view('admin.gallery.categories.edit', compact('category'));
    }

    public function update(Request $request, GalleryCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $this->generateSlug($data['name'], $data['slug'] ?? null, $category->id);

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.gallery.categories.index')
            ->with('status', 'Kategori galeri berhasil diperbarui.');
    }

    public function destroy(GalleryCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('admin.gallery.categories.index')
            ->with('status', 'Kategori galeri berhasil dihapus.');
    }

    protected function generateSlug(string $name, ?string $requested, ?int $ignoreId = null): string
    {
        $base = Str::slug($requested ?: $name);
        if ($base === '') {
            $base = 'galeri';
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = GalleryCategory::where('slug', $slug);
        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
