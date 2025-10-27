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
use Illuminate\Support\Collection;

class PageController extends Controller
{
    public function home()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('home', $theme));
        $defaultSections = PageElements::sections('home', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        return view('admin.pages.home', compact(
            'sections',
            'settings',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('home', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function product()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('product', $theme));
        $defaultSections = PageElements::sections('product', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        return view('admin.pages.product', compact(
            'sections',
            'settings',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('product', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function productDetail()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('product-detail', $theme));
        $defaultSections = PageElements::sections('product-detail', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        $previewProduct = Product::first();
        $previewUrl = $previewProduct ? route('products.show', $previewProduct) : null;
        $comments = Comment::with(['product', 'user'])->latest()->get();

        return view('admin.pages.product-detail', compact(
            'sections',
            'settings',
            'comments',
            'previewUrl',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('product-detail', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function article()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('article', $theme));
        $articles = collect(json_decode($settings->get('articles.items', '[]'), true));
        $defaultSections = PageElements::sections('article', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        $previewUrl = route('articles.index');

        return view('admin.pages.article', compact(
            'sections',
            'settings',
            'previewUrl',
            'articles',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('article', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function articleDetail()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('article-detail', $theme));
        $articleSettings = collect(PageSetting::forPage('article', $theme));
        $articles = collect(json_decode($articleSettings->get('articles.items', '[]'), true));
        $previewArticle = $articles->first(function ($item) {
            return ! empty($item['slug']);
        });
        $previewUrl = $previewArticle ? route('articles.show', ['slug' => $previewArticle['slug']]) : null;
        $defaultSections = PageElements::sections('article-detail', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        return view('admin.pages.article-detail', compact(
            'sections',
            'settings',
            'previewUrl',
            'articles',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('article-detail', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function gallery()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('gallery', $theme));
        $defaultSections = PageElements::sections('gallery', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);
        $previewUrl = route('gallery.index');

        return view('admin.pages.gallery', compact(
            'sections',
            'settings',
            'previewUrl',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('gallery', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function contact()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('contact', $theme));
        $defaultSections = PageElements::sections('contact', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);
        $previewUrl = route('contact');

        return view('admin.pages.contact', compact(
            'sections',
            'settings',
            'previewUrl',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
    }

    public function updateContact(Request $request)
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

        PageSetting::put('contact', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function about()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('about', $theme));
        $defaultSections = PageElements::sections('about', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        $previewUrl = route('about');

        return view('admin.pages.about', compact(
            'sections',
            'settings',
            'previewUrl',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('about', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function cart()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('cart', $theme));
        $defaultSections = PageElements::sections('cart', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        $previewUrl = route('cart.index');

        return view('admin.pages.cart', compact(
            'sections',
            'settings',
            'previewUrl',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('cart', $key, $value, $theme);

        return response()->json(['status' => 'ok']);
    }

    public function layout()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('layout', $theme));
        if (! $settings->has('navigation.link.articles')) {
            $settings->put('navigation.link.articles', '1');
        }
        if (! $settings->has('navigation.link.article-detail')) {
            $settings->put('navigation.link.article-detail', '0');
        }
        if (! $settings->has('navigation.link.gallery')) {
            $settings->put('navigation.link.gallery', '1');
        }
        if (! $settings->has('navigation.link.contact')) {
            $settings->put('navigation.link.contact', '1');
        }
        $defaultSections = PageElements::sections('layout', $theme);
        [$sections, $availableSections, $composition, $defaultComposition, $sectionLabels] =
            $this->resolveSectionConfig($theme, $settings, $defaultSections);

        $previewUrl = url('/');

        return view('admin.pages.layout', compact(
            'sections',
            'settings',
            'previewUrl',
            'theme',
            'availableSections',
            'composition',
            'defaultComposition',
            'sectionLabels'
        ));
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

        PageSetting::put('layout', $key, $value, $theme);

        LayoutSettings::flushCache();

        return response()->json(['status' => 'ok']);
    }

    public function toggleComment(Comment $comment)
    {
        $comment->is_active = ! $comment->is_active;
        $comment->save();

        return back()->with('success', 'Status komentar diperbarui.');
    }

    /**
     * Resolve the available, active, and default sections for a page editor.
     *
     * @param Collection $settings
     * @param array<string, array> $defaultSections
     * @return array{
     *     0: array<string, array>,
     *     1: array<int, array{key: string, label: string}>,
     *     2: array<int, string>,
     *     3: array<int, string>,
     *     4: array<string, string>
     * }
     */
    protected function resolveSectionConfig(string $theme, Collection $settings, array $defaultSections = []): array
    {
        $allSections = $this->resolveThemeSections($theme);

        if ($defaultSections !== []) {
            foreach ($defaultSections as $key => $definition) {
                $allSections[$key] = $definition;
            }
        }

        if ($allSections === []) {
            return [[], [], [], [], []];
        }

        $defaultOrder = array_keys($defaultSections);

        $rawComposition = $settings->get('__sections');
        $composition = [];
        $hasCustomComposition = false;

        if (is_string($rawComposition) && $rawComposition !== '') {
            $decoded = json_decode($rawComposition, true);
            if (is_array($decoded)) {
                $hasCustomComposition = true;

                foreach ($decoded as $key) {
                    if (
                        is_string($key)
                        && array_key_exists($key, $allSections)
                        && ! in_array($key, $composition, true)
                    ) {
                        $composition[] = $key;
                    }
                }

                if ($decoded !== [] && $composition === []) {
                    $hasCustomComposition = false;
                }
            }
        }

        if ($composition === []) {
            $composition = $hasCustomComposition ? [] : $defaultOrder;
        }

        $sections = [];
        foreach ($composition as $key) {
            if (isset($allSections[$key])) {
                $sections[$key] = $allSections[$key];
            }
        }

        $availableSections = [];
        foreach ($allSections as $key => $section) {
            if (! in_array($key, $composition, true)) {
                $availableSections[] = [
                    'key' => $key,
                    'label' => $section['label'] ?? ucfirst($key),
                ];
            }
        }

        $labels = [];
        foreach ($allSections as $key => $section) {
            $labels[$key] = $section['label'] ?? ucfirst($key);
        }

        return [$sections, $availableSections, $composition, $defaultOrder, $labels];
    }

    /**
     * Gather the section definitions available across an entire theme.
     *
     * @return array<string, array>
     */
    protected function resolveThemeSections(string $theme): array
    {
        $definitions = PageElements::definitions();

        if ($definitions === []) {
            return [];
        }

        $resolved = [];

        foreach (array_keys($definitions) as $pageKey) {
            $sections = PageElements::sections($pageKey, $theme);

            foreach ($sections as $sectionKey => $sectionDefinition) {
                if (! array_key_exists($sectionKey, $resolved)) {
                    $resolved[$sectionKey] = $sectionDefinition;
                    continue;
                }

                $existing = $resolved[$sectionKey];
                $existingElements = [];

                foreach ($existing['elements'] ?? [] as $element) {
                    if (isset($element['id'])) {
                        $existingElements[$element['id']] = $element;
                    }
                }

                foreach ($sectionDefinition['elements'] ?? [] as $element) {
                    if (isset($element['id'])) {
                        $existingElements[$element['id']] = $element;
                    }
                }

                $resolved[$sectionKey]['elements'] = array_values($existingElements);

                $existingLabel = $resolved[$sectionKey]['label'] ?? null;
                if (
                    (! is_string($existingLabel) || $existingLabel === '' || $existingLabel === ucfirst($sectionKey))
                    && ! empty($sectionDefinition['label'])
                ) {
                    $resolved[$sectionKey]['label'] = $sectionDefinition['label'];
                }
            }
        }

        return $resolved;
    }
}
