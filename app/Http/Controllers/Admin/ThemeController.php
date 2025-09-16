<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Theme;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ThemeController extends Controller
{
    public function index(Request $request)
    {
        $dirs = array_map('basename', File::directories(base_path('themes')));
        foreach ($dirs as $dir) {
            $json = @json_decode(File::get(base_path("themes/{$dir}/theme.json")), true) ?: [];
            $display = $json['displayName'] ?? ucfirst(substr($dir, 6));
            Theme::updateOrCreate(
                ['name' => $dir],
                ['display_name' => $display]
            );
        }

        $query = Theme::query()->with('tags');
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }
        $themes = $query->get();

        $tags = Tag::all();
        $active = Setting::getValue('active_theme', 'theme-herbalgreen');
        $preview = route('admin.themes.preview', ['theme' => $active]);

        return view('admin.themes.index', [
            'themes' => $themes,
            'active' => $active,
            'tags' => $tags,
            'preview' => $preview,
            'selectedTag' => $request->tag
        ]);
    }

    public function preview(string $theme)
    {
        $viewPath = base_path("themes/{$theme}/views/home.blade.php");
        if (!File::exists($viewPath)) {
            abort(404);
        }

        $source = base_path("themes/{$theme}/assets");
        $destination = public_path("themes/{$theme}");
        if (File::exists($source) && !File::exists($destination)) {
            File::ensureDirectoryExists($destination);
            File::copyDirectory($source, $destination);
        }

        return view()->file($viewPath, ['theme' => $theme]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|string'
        ]);

        $theme = $request->input('theme');

        Setting::updateOrCreate(
            ['key' => 'active_theme'],
            ['value' => $theme]
        );

        $source = base_path("themes/{$theme}/assets");
        $destination = public_path("themes/{$theme}");
        if (File::exists($source)) {
            File::ensureDirectoryExists($destination);
            File::copyDirectory($source, $destination);
        }

        return redirect()->route('admin.themes.index')->with('success', 'Theme updated.');
    }
}

