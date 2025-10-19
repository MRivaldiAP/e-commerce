<?php

namespace App\Http\Controllers;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    public function index()
    {
        $activeTheme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $viewPath = base_path("themes/{$activeTheme}/views/gallery.blade.php");

        if (! File::exists($viewPath)) {
            abort(404);
        }

        $categories = GalleryCategory::orderBy('name')->get();
        $items = GalleryItem::with('category')
            ->orderByRaw('position IS NULL')
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get();

        return view()->file($viewPath, [
            'theme' => $activeTheme,
            'categories' => $categories,
            'items' => $items,
        ]);
    }
}
