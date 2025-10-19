<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryItemController extends Controller
{
    public function index(): View
    {
        $items = GalleryItem::with('category')
            ->orderByRaw('position IS NULL')
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.gallery.items.index', compact('items'));
    }

    public function create(): View
    {
        $categories = GalleryCategory::orderBy('name')->get();

        return view('admin.gallery.items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'gallery_category_id' => ['nullable', 'exists:gallery_categories,id'],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('image')->store('gallery/items', 'public');

        GalleryItem::create([
            'title' => $data['title'],
            'gallery_category_id' => $data['gallery_category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'position' => $data['position'] ?? null,
            'image_path' => $path,
        ]);

        return redirect()
            ->route('admin.gallery.items.index')
            ->with('status', 'Item galeri berhasil ditambahkan.');
    }

    public function edit(GalleryItem $item): View
    {
        $categories = GalleryCategory::orderBy('name')->get();

        return view('admin.gallery.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, GalleryItem $item): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'gallery_category_id' => ['nullable', 'exists:gallery_categories,id'],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $payload = [
            'title' => $data['title'],
            'gallery_category_id' => $data['gallery_category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'position' => $data['position'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('gallery/items', 'public');
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $payload['image_path'] = $path;
        }

        $item->update($payload);

        return redirect()
            ->route('admin.gallery.items.index')
            ->with('status', 'Item galeri berhasil diperbarui.');
    }

    public function destroy(GalleryItem $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()
            ->route('admin.gallery.items.index')
            ->with('status', 'Item galeri berhasil dihapus.');
    }
}
