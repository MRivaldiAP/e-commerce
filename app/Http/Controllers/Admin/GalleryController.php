<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index()
    {
        $settings = collect(PageSetting::forPage('gallery'));
        $categories = collect(json_decode($settings->get('gallery.categories', '[]'), true))
            ->filter(fn ($item) => is_array($item));
        $items = collect(json_decode($settings->get('gallery.items', '[]'), true))
            ->filter(fn ($item) => is_array($item));

        $categoryMap = $categories->keyBy(fn ($item) => $item['slug'] ?? '');

        return view('admin.gallery.index', [
            'categories' => $categories->values(),
            'items' => $items->values(),
            'categoryMap' => $categoryMap,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
        ]);

        $settings = collect(PageSetting::forPage('gallery'));
        $categories = collect(json_decode($settings->get('gallery.categories', '[]'), true))
            ->filter(fn ($item) => is_array($item));

        if ($categories->contains(fn ($category) => ($category['slug'] ?? null) === $data['slug'])) {
            return back()->withErrors(['slug' => 'Slug kategori sudah digunakan.'])->withInput();
        }

        $categories = $categories->push($data)->values();

        PageSetting::put('gallery', 'gallery.categories', $categories->toJson());

        return redirect()->route('admin.gallery.index')->with('success', 'Kategori galeri ditambahkan.');
    }

    public function destroyCategory(string $slug)
    {
        $settings = collect(PageSetting::forPage('gallery'));
        $categories = collect(json_decode($settings->get('gallery.categories', '[]'), true))
            ->filter(fn ($item) => is_array($item));
        $items = collect(json_decode($settings->get('gallery.items', '[]'), true))
            ->filter(fn ($item) => is_array($item));

        $categories = $categories->reject(fn ($category) => ($category['slug'] ?? null) === $slug)->values();

        [$itemsToKeep, $itemsToRemove] = $items->partition(fn ($item) => ($item['category'] ?? null) !== $slug);

        PageSetting::put('gallery', 'gallery.categories', $categories->toJson());
        PageSetting::put('gallery', 'gallery.items', $itemsToKeep->values()->toJson());

        $itemsToRemove
            ->pluck('image')
            ->filter()
            ->each(fn ($path) => $this->deleteImage((string) $path));

        return redirect()->route('admin.gallery.index')->with('success', 'Kategori galeri dihapus. Item terkait juga dihapus.');
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $settings = collect(PageSetting::forPage('gallery'));
        $categories = collect(json_decode($settings->get('gallery.categories', '[]'), true))
            ->filter(fn ($item) => is_array($item));

        if (! $categories->contains(fn ($category) => ($category['slug'] ?? null) === $data['category'])) {
            return back()->withErrors(['category' => 'Kategori tidak ditemukan.'])->withInput();
        }

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $storedPath = $request->file('image')->store("gallery/{$theme}", 'public');

        $items = collect(json_decode($settings->get('gallery.items', '[]'), true))
            ->filter(fn ($item) => is_array($item));

        $items = $items->push([
            'id' => (string) Str::uuid(),
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'] ?? '',
            'image' => $storedPath,
        ])->values();

        PageSetting::put('gallery', 'gallery.items', $items->toJson());

        return redirect()->route('admin.gallery.index')->with('success', 'Item galeri ditambahkan.');
    }

    public function destroyItem(string $id)
    {
        $settings = collect(PageSetting::forPage('gallery'));
        $items = collect(json_decode($settings->get('gallery.items', '[]'), true))
            ->filter(fn ($item) => is_array($item));

        $item = $items->firstWhere('id', $id);

        $items = $items->reject(fn ($value) => ($value['id'] ?? null) === $id)->values();

        PageSetting::put('gallery', 'gallery.items', $items->toJson());

        if ($item && ! empty($item['image'])) {
            $this->deleteImage((string) $item['image']);
        }

        return redirect()->route('admin.gallery.index')->with('success', 'Item galeri dihapus.');
    }

    protected function deleteImage(string $path): void
    {
        $normalized = ltrim($path, '/');

        if ($normalized === '' || Str::startsWith($normalized, ['http://', 'https://'])) {
            return;
        }

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/')) ?: '';
        }

        if ($normalized === '') {
            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
