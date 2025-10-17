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
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();
        $categories = GalleryCategory::orderBy('name')->get();

        return view('admin.gallery.items.index', compact('items', 'categories'));
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
            'image' => ['required', 'image'],
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        GalleryItem::create([
            'title' => $data['title'],
            'gallery_category_id' => $data['gallery_category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'position' => $data['position'] ?? 0,
            'image_path' => $path,
        ]);

        return redirect()
            ->route('admin.gallery.items.index')
            ->with('status', 'Item galeri berhasil disimpan.');
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
            'image' => ['nullable', 'image'],
        ]);

        $payload = [
            'title' => $data['title'],
            'gallery_category_id' => $data['gallery_category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'position' => $data['position'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
            $payload['image_path'] = $request->file('image')->store('gallery', 'public');
        }

        $item->update($payload);

        return redirect()
            ->route('admin.gallery.items.index')
            ->with('status', 'Item galeri diperbarui.');
    }

    public function destroy(GalleryItem $item): RedirectResponse
    {
        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()
            ->route('admin.gallery.items.index')
            ->with('status', 'Item galeri dihapus.');
    }
}
