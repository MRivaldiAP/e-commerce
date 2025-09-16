<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSetting;
use App\Models\Setting;
use App\Models\Comment;
use App\Models\Product;

class PageController extends Controller
{
    public function home()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = PageSetting::where('theme', $theme)->where('page', 'home')->pluck('value', 'key');
        $sections = [
            'navigation' => [
                'label' => 'Navigation',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Homepage link', 'id' => 'navigation.home'],
                    ['type' => 'checkbox', 'label' => 'Tea Collection link', 'id' => 'navigation.products'],
                    ['type' => 'checkbox', 'label' => 'News link', 'id' => 'navigation.news'],
                    ['type' => 'checkbox', 'label' => 'Contact Us link', 'id' => 'navigation.contact'],
                ],
            ],
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'hero.visible'],
                    ['type' => 'image', 'label' => 'Main Image', 'id' => 'hero.image'],
                    ['type' => 'image', 'label' => 'Spinning Image', 'id' => 'hero.spin_image'],
                    ['type' => 'text', 'label' => 'Spinning Text', 'id' => 'hero.spin_text'],
                    ['type' => 'text', 'label' => 'Tagline', 'id' => 'hero.tagline'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'hero.heading'],
                    ['type' => 'textarea', 'label' => 'Description', 'id' => 'hero.description'],
                    ['type' => 'text', 'label' => 'Button Label', 'id' => 'hero.button_label'],
                    ['type' => 'text', 'label' => 'Button Link', 'id' => 'hero.button_link'],
                ],
            ],
            'about' => [
                'label' => 'About',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'about.visible'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'about.heading'],
                    ['type' => 'image', 'label' => 'Image', 'id' => 'about.image'],
                    ['type' => 'textarea', 'label' => 'Text', 'id' => 'about.text'],
                ],
            ],
            'products' => [
                'label' => 'Products',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'products.visible'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'products.heading'],
                ],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'testimonials.visible'],
                    ['type' => 'repeatable', 'id' => 'testimonials.items', 'fields' => [
                        ['name' => 'name', 'placeholder' => 'Name'],
                        ['name' => 'title', 'placeholder' => 'Title'],
                        ['name' => 'text', 'placeholder' => 'Testimonial', 'type' => 'textarea'],
                        ['name' => 'photo', 'placeholder' => 'Photo', 'type' => 'image'],
                    ]],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'services.visible'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'services.heading'],
                    ['type' => 'repeatable', 'id' => 'services.items', 'fields' => [
                        ['name' => 'icon', 'placeholder' => 'Icon class'],
                        ['name' => 'title', 'placeholder' => 'Service Title'],
                        ['name' => 'text', 'placeholder' => 'Description', 'type' => 'textarea'],
                    ]],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'contact.visible'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'contact.heading'],
                    ['type' => 'textarea', 'label' => 'Map Embed', 'id' => 'contact.map'],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Privacy Policy link', 'id' => 'footer.privacy'],
                    ['type' => 'checkbox', 'label' => 'Terms & Conditions link', 'id' => 'footer.terms'],
                    ['type' => 'text', 'label' => 'Copyright Text', 'id' => 'footer.copyright'],
                ],
            ],
        ];

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

        PageSetting::updateOrCreate(
            ['theme' => $theme, 'page' => 'home', 'key' => $request->input('key')],
            ['value' => $value]
        );

        return response()->json(['status' => 'ok']);
    }

    public function product()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = PageSetting::where('theme', $theme)->where('page', 'product')->pluck('value', 'key');

        $sections = [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'hero.visible'],
                    ['type' => 'image', 'label' => 'Background Image', 'id' => 'hero.image'],
                    ['type' => 'text', 'label' => 'Title', 'id' => 'title'],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Privacy Policy link', 'id' => 'footer.privacy'],
                    ['type' => 'checkbox', 'label' => 'Terms & Conditions link', 'id' => 'footer.terms'],
                    ['type' => 'text', 'label' => 'Copyright Text', 'id' => 'footer.copyright'],
                ],
            ],
        ];

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

        PageSetting::updateOrCreate(
            ['theme' => $theme, 'page' => 'product', 'key' => $request->input('key')],
            ['value' => $value]
        );

        return response()->json(['status' => 'ok']);
    }

    public function productDetail()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = PageSetting::where('theme', $theme)->where('page', 'product-detail')->pluck('value', 'key');

        $sections = [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'hero.visible'],
                    ['type' => 'image', 'label' => 'Background Image', 'id' => 'hero.image'],
                    ['type' => 'text', 'label' => 'Breadcrumb Title', 'id' => 'hero.title'],
                ],
            ],
            'comments' => [
                'label' => 'Comments',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'comments.visible'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'comments.heading'],
                ],
            ],
            'recommendations' => [
                'label' => 'Recommendations',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'recommendations.visible'],
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'recommendations.heading'],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Privacy Policy link', 'id' => 'footer.privacy'],
                    ['type' => 'checkbox', 'label' => 'Terms & Conditions link', 'id' => 'footer.terms'],
                    ['type' => 'text', 'label' => 'Copyright Text', 'id' => 'footer.copyright'],
                ],
            ],
        ];

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

        PageSetting::updateOrCreate(
            ['theme' => $theme, 'page' => 'product-detail', 'key' => $request->input('key')],
            ['value' => $value]
        );

        return response()->json(['status' => 'ok']);
    }

    public function toggleComment(Comment $comment)
    {
        $comment->is_active = ! $comment->is_active;
        $comment->save();

        return back()->with('success', 'Status komentar diperbarui.');
    }
}
