<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Theme;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::with('themes')->get();
        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        $themes = Theme::all();
        return view('admin.tags.create', compact('themes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:tags,name',
            'themes' => 'array'
        ]);
        $tag = Tag::create(['name' => $data['name']]);
        if (!empty($data['themes'])) {
            $tag->themes()->attach($data['themes']);
        }
        return redirect()->route('admin.tags.index')->with('success', 'Tag created.');
    }

    public function edit(Tag $tag)
    {
        $themes = Theme::all();
        $tag->load('themes');
        return view('admin.tags.edit', compact('tag', 'themes'));
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:tags,name,' . $tag->id,
            'themes' => 'array'
        ]);
        $tag->update(['name' => $data['name']]);
        $tag->themes()->sync($data['themes'] ?? []);
        return redirect()->route('admin.tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('admin.tags.index')->with('success', 'Tag deleted.');
    }
}
