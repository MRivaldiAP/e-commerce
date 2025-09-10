<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = array_map('basename', File::directories(base_path('themes')));
        $active = Setting::getValue('active_theme', 'theme-herbalgreen');
        return view('admin.themes.index', compact('themes', 'active'));
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

