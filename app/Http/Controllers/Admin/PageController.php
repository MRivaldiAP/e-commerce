<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSetting;
use App\Models\Setting;

class PageController extends Controller
{
    public function home()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = PageSetting::where('theme', $theme)->where('page', 'home')->pluck('value', 'key');
        $sections = [
            'topbar' => [
                'label' => 'Shipping Bar',
                'elements' => [
                    ['type' => 'text', 'label' => 'Text', 'id' => 'topbar.text'],
                    ['type' => 'checkbox', 'label' => 'Show Bar', 'id' => 'topbar.visible'],
                ],
            ],
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
                    ['type' => 'image', 'label' => 'Background Image', 'id' => 'hero.image'],
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
                    ['type' => 'text', 'label' => 'Heading', 'id' => 'about.heading'],
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
                    ['type' => 'repeatable', 'id' => 'testimonials.items'],
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
}
