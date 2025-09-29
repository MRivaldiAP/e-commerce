<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSetting;
use App\Models\Setting;
use App\Models\Comment;
use App\Models\Product;
use App\Support\LayoutSettings;

class PageController extends Controller
{
    public function home()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('home'));
        $sections = [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'hero.visible'],
                    ['type' => 'checkbox', 'label' => 'Use Dark Overlay', 'id' => 'hero.mask'],
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

        $key = $request->input('key');

        PageSetting::put('home', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function product()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('product'));

        $sections = [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'hero.visible'],
                    ['type' => 'checkbox', 'label' => 'Use Dark Overlay', 'id' => 'hero.mask'],
                    ['type' => 'image', 'label' => 'Background Image', 'id' => 'hero.image'],
                    ['type' => 'text', 'label' => 'Title', 'id' => 'title'],
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

        $key = $request->input('key');

        PageSetting::put('product', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function productDetail()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('product-detail'));

        $sections = [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Show Section', 'id' => 'hero.visible'],
                    ['type' => 'checkbox', 'label' => 'Use Dark Overlay', 'id' => 'hero.mask'],
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

        $key = $request->input('key');

        PageSetting::put('product-detail', $key, $value);

        return response()->json(['status' => 'ok']);
    }

    public function about()
    {
        $theme = Setting::getValue('active_theme', 'theme-herbalgreen');
        $settings = collect(PageSetting::forPage('about'));

        $sections = [
            'hero' => [
                'label' => 'Header',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Seksi', 'id' => 'hero.visible'],
                    ['type' => 'checkbox', 'label' => 'Gunakan Masker Gelap', 'id' => 'hero.mask'],
                    ['type' => 'image', 'label' => 'Gambar Latar', 'id' => 'hero.background'],
                    ['type' => 'text', 'label' => 'Judul', 'id' => 'hero.heading'],
                    ['type' => 'textarea', 'label' => 'Deskripsi Singkat', 'id' => 'hero.text'],
                ],
            ],
            'intro' => [
                'label' => 'Tentang Kami',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Seksi', 'id' => 'intro.visible'],
                    ['type' => 'image', 'label' => 'Gambar', 'id' => 'intro.image'],
                    ['type' => 'text', 'label' => 'Judul Seksi', 'id' => 'intro.heading'],
                    ['type' => 'textarea', 'label' => 'Deskripsi', 'id' => 'intro.description'],
                ],
            ],
            'quote' => [
                'label' => 'Quote',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Seksi', 'id' => 'quote.visible'],
                    ['type' => 'textarea', 'label' => 'Teks Quote', 'id' => 'quote.text'],
                    ['type' => 'text', 'label' => 'Nama Pengutip', 'id' => 'quote.author'],
                ],
            ],
            'team' => [
                'label' => 'Tim Kami',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Seksi', 'id' => 'team.visible'],
                    ['type' => 'text', 'label' => 'Judul Seksi', 'id' => 'team.heading'],
                    ['type' => 'textarea', 'label' => 'Deskripsi Pendek', 'id' => 'team.description'],
                    ['type' => 'repeatable', 'id' => 'team.members', 'fields' => [
                        ['name' => 'name', 'placeholder' => 'Nama'],
                        ['name' => 'title', 'placeholder' => 'Jabatan'],
                        ['name' => 'photo', 'placeholder' => 'Path Foto'],
                        ['name' => 'description', 'placeholder' => 'Deskripsi', 'type' => 'textarea'],
                    ]],
                ],
            ],
            'advantages' => [
                'label' => 'Keunggulan Kami',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Seksi', 'id' => 'advantages.visible'],
                    ['type' => 'text', 'label' => 'Judul Seksi', 'id' => 'advantages.heading'],
                    ['type' => 'textarea', 'label' => 'Deskripsi Pendek', 'id' => 'advantages.description'],
                    ['type' => 'repeatable', 'id' => 'advantages.items', 'fields' => [
                        ['name' => 'icon', 'placeholder' => 'Kelas Ikon (contoh: fa fa-leaf)'],
                        ['name' => 'title', 'placeholder' => 'Judul Keunggulan'],
                        ['name' => 'text', 'placeholder' => 'Deskripsi', 'type' => 'textarea'],
                    ]],
                ],
            ],
        ];

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

        $sections = [
            'header' => [
                'label' => 'Header',
                'elements' => [
                    ['type' => 'text', 'label' => 'Judul Halaman', 'id' => 'title'],
                    ['type' => 'textarea', 'label' => 'Deskripsi Singkat', 'id' => 'subtitle'],
                ],
            ],
            'empty' => [
                'label' => 'Keranjang Kosong',
                'elements' => [
                    ['type' => 'textarea', 'label' => 'Pesan Keranjang Kosong', 'id' => 'empty.message'],
                    ['type' => 'text', 'label' => 'Label Tombol Belanja', 'id' => 'empty.button'],
                ],
            ],
            'actions' => [
                'label' => 'Tombol Aksi',
                'elements' => [
                    ['type' => 'text', 'label' => 'Label Tombol Pengiriman', 'id' => 'button.shipping'],
                    ['type' => 'text', 'label' => 'Label Tombol Pembayaran', 'id' => 'button.payment'],
                ],
            ],
        ];

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

        $sections = [
            'navigation' => [
                'label' => 'Navigasi',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Brand', 'id' => 'navigation.brand.visible'],
                    ['type' => 'text', 'label' => 'Nama Brand', 'id' => 'navigation.brand.text'],
                    ['type' => 'image', 'label' => 'Logo Brand', 'id' => 'navigation.brand.logo'],
                    ['type' => 'text', 'label' => 'Kelas Ikon Brand', 'id' => 'navigation.brand.icon'],
                    ['type' => 'checkbox', 'label' => 'Tautan Home', 'id' => 'navigation.link.home'],
                    ['type' => 'checkbox', 'label' => 'Tautan Tentang Kami', 'id' => 'navigation.link.about'],
                    ['type' => 'checkbox', 'label' => 'Tautan Produk', 'id' => 'navigation.link.products'],
                    ['type' => 'checkbox', 'label' => 'Tautan Pesanan Saya', 'id' => 'navigation.link.orders'],
                    ['type' => 'checkbox', 'label' => 'Ikon Keranjang', 'id' => 'navigation.icon.cart'],
                    ['type' => 'checkbox', 'label' => 'Tombol Login', 'id' => 'navigation.button.login'],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Tampilkan Hot Links', 'id' => 'footer.hotlinks.visible'],
                    ['type' => 'checkbox', 'label' => 'Tampilkan Alamat', 'id' => 'footer.address.visible'],
                    ['type' => 'textarea', 'label' => 'Alamat', 'id' => 'footer.address.text'],
                    ['type' => 'checkbox', 'label' => 'Tampilkan Nomor Telepon', 'id' => 'footer.phone.visible'],
                    ['type' => 'text', 'label' => 'Nomor Telepon', 'id' => 'footer.phone.text'],
                    ['type' => 'checkbox', 'label' => 'Tampilkan Email', 'id' => 'footer.email.visible'],
                    ['type' => 'text', 'label' => 'Email', 'id' => 'footer.email.text'],
                    ['type' => 'checkbox', 'label' => 'Tampilkan Link Media Sosial', 'id' => 'footer.social.visible'],
                    ['type' => 'text', 'label' => 'Link Media Sosial', 'id' => 'footer.social.text'],
                    ['type' => 'checkbox', 'label' => 'Tampilkan Jam Operasional', 'id' => 'footer.schedule.visible'],
                    ['type' => 'text', 'label' => 'Jam Operasional', 'id' => 'footer.schedule.text'],
                    ['type' => 'text', 'label' => 'Teks Hak Cipta', 'id' => 'footer.copyright'],
                ],
            ],
        ];

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
