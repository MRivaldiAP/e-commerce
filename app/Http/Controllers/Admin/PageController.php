<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSetting;
use App\Models\Setting;
use App\Models\Comment;
use App\Models\Product;
use App\Support\LayoutSettings;
use App\Support\PageElements;

class PageController extends Controller
{
    public function home()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('home'));
        $sections = PageElements::sections('home', $theme);

        return view('admin.pages.home', compact('sections', 'settings'));
    }

    public function updateHome(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('home', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function product()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('product'));
        $sections = PageElements::sections('product', $theme);

        return view('admin.pages.product', compact('sections', 'settings'));
    }

    public function updateProduct(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('product', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function productDetail()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('product-detail'));
        $sections = PageElements::sections('product-detail', $theme);

        $previewProduct = Product::first();
        $previewUrl = $previewProduct ? route('products.show', $previewProduct) : null;
        $comments = Comment::with(['product', 'user'])->latest()->get();

        return view('admin.pages.product-detail', compact('sections', 'settings', 'comments', 'previewUrl'));
    }

    public function updateProductDetail(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('product-detail', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function article()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('article'));
        $articles = collect(json_decode($settings->get('articles.items', '[]'), true));
        $sections = PageElements::sections('article', $theme);

        $previewUrl = route('articles.index');

        return view('admin.pages.article', compact('sections', 'settings', 'previewUrl', 'articles'));
    }

    public function updateArticle(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('article', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function articleDetail()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('article-detail'));
        $articleSettings = collect(PageSetting::forPage('article'));
        $articles = collect(json_decode($articleSettings->get('articles.items', '[]'), true));
        $previewArticle = $articles->first(function ($item) {
            return ! empty($item['slug']);
        });
        $previewUrl = $previewArticle ? route('articles.show', ['slug' => $previewArticle['slug']]) : null;
        $sections = PageElements::sections('article-detail', $theme);

        return view('admin.pages.article-detail', compact('sections', 'settings', 'previewUrl', 'articles'));
    }

    public function updateArticleDetail(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('article-detail', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function gallery()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('gallery'));
        $sections = PageElements::sections('gallery', $theme);
        $previewUrl = route('gallery');

        return view('admin.pages.gallery', compact('sections', 'settings', 'previewUrl'));
    }

    public function updateGallery(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('gallery', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function about()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('about'));
        $sections = PageElements::sections('about', $theme);

        $previewUrl = route('about');

        return view('admin.pages.about', compact('sections', 'settings', 'previewUrl'));
    }

    public function updateAbout(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('about', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function cart()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('cart'));
        $sections = PageElements::sections('cart', $theme);

        $previewUrl = route('cart.index');

        return view('admin.pages.cart', compact('sections', 'settings', 'previewUrl'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('cart', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function layout()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('layout'));
        if (! $settings->has('navigation.link.articles')) {
            $settings->put('navigation.link.articles', '1');
        }
        if (! $settings->has('navigation.link.article-detail')) {
            $settings->put('navigation.link.article-detail', '0');
        }
        $sections = PageElements::sections('layout', $theme);

        $previewUrl = url('/');

        return view('admin.pages.layout', compact('sections', 'settings', 'previewUrl'));
    }

    public function updateLayout(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'nullable',
        ]);

        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');

        $value = $request->input('value');
        if ($request->hasFile('value')) {
            $value = $request->file('value')->store("pages/{$theme}", 'public');
        }

        $key = $request->input('key');

        PageSetting::put('layout', $key, $value);

        LayoutSettings::flushCache();

        return response()->json(['status' => 'ok']);
    }

    public function toggleComment(Comment $comment)
    {
        $comment->is_active = ! $comment->is_active;
        $comment->save();

        return back()->with('success', 'Status komentar diperbarui.');
    }
}
