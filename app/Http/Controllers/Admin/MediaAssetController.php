<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaAssetController extends Controller
{
    public function index(): View
    {
        $assets = MediaAsset::orderByDesc('created_at')->paginate(20);

        return view('admin.media.index', compact('assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:5120'],
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        MediaAsset::create([
            'name' => $data['name'],
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Media berhasil diunggah.');
    }

    public function destroy(MediaAsset $medium): RedirectResponse
    {
        if ($medium->file_path) {
            Storage::disk('public')->delete($medium->file_path);
        }

        $medium->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Media berhasil dihapus.');
    }
}
