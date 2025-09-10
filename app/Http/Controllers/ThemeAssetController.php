<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class ThemeAssetController extends Controller
{
    /**
     * Serve asset files from a theme directory outside the public folder.
     */
    public function __invoke(string $theme, string $path)
    {
        $assetPath = base_path("themes/{$theme}/assets/{$path}");
        if (!File::exists($assetPath)) {
            abort(404);
        }

        return response(File::get($assetPath), 200)
            ->header('Content-Type', File::mimeType($assetPath));
    }
}
