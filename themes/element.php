<?php

return [
    'home' => [
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Use Dark Overlay'],
                    'hero.image' => ['type' => 'image', 'label' => 'Main Image'],
                    'hero.spin_image' => ['type' => 'image', 'label' => 'Spinning Image'],
                    'hero.spin_text' => ['type' => 'text', 'label' => 'Spinning Text'],
                    'hero.tagline' => ['type' => 'text', 'label' => 'Tagline'],
                    'hero.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Description'],
                    'hero.button_label' => ['type' => 'text', 'label' => 'Button Label'],
                    'hero.button_link' => ['type' => 'text', 'label' => 'Button Link'],
                    'hero.slides' => [
                        'type' => 'repeatable',
                        'label' => 'Hero Slides',
                        'fields' => [
                            ['name' => 'image', 'placeholder' => 'Slide Image', 'type' => 'image'],
                        ],
                    ],
                    'hero.highlights' => [
                        'type' => 'repeatable',
                        'label' => 'Highlights',
                        'fields' => [
                            ['name' => 'icon', 'placeholder' => 'Icon class'],
                            ['name' => 'label', 'placeholder' => 'Highlight text'],
                        ],
                    ],
                ],
            ],
            'about' => [
                'label' => 'About',
                'elements' => [
                    'about.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'about.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'about.image' => ['type' => 'image', 'label' => 'Image'],
                    'about.image_primary' => ['type' => 'image', 'label' => 'Primary Image'],
                    'about.image_secondary' => ['type' => 'image', 'label' => 'Secondary Image'],
                    'about.badge_text' => ['type' => 'text', 'label' => 'Badge Text'],
                    'about.text' => ['type' => 'textarea', 'label' => 'Description'],
                    'about.text_primary' => ['type' => 'textarea', 'label' => 'Primary Description'],
                    'about.text_secondary' => ['type' => 'textarea', 'label' => 'Secondary Description'],
                    'about.checklist' => [
                        'type' => 'repeatable',
                        'label' => 'Checklist',
                        'fields' => [
                            ['name' => 'text', 'placeholder' => 'Checklist item'],
                        ],
                    ],
                ],
            ],
            'features' => [
                'label' => 'Features',
                'elements' => [
                    'features.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'features.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'features.items' => [
                        'type' => 'repeatable',
                        'label' => 'Feature Items',
                        'fields' => [
                            ['name' => 'icon', 'placeholder' => 'Icon class'],
                            ['name' => 'title', 'placeholder' => 'Title'],
                            ['name' => 'text', 'placeholder' => 'Description', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],
            'projects' => [
                'label' => 'Projects',
                'elements' => [
                    'projects.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'projects.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'projects.subheading' => ['type' => 'textarea', 'label' => 'Description'],
                    'projects.items' => [
                        'type' => 'repeatable',
                        'label' => 'Project Items',
                        'fields' => [
                            ['name' => 'image', 'placeholder' => 'Image', 'type' => 'image'],
                            ['name' => 'title', 'placeholder' => 'Project Title'],
                            ['name' => 'count', 'placeholder' => 'Project Count'],
                            ['name' => 'link', 'placeholder' => 'Link URL'],
                        ],
                    ],
                ],
            ],
            'products' => [
                'label' => 'Products',
                'elements' => [
                    'products.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'products.heading' => ['type' => 'text', 'label' => 'Heading'],
                ],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'elements' => [
                    'testimonials.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'testimonials.items' => [
                        'type' => 'repeatable',
                        'label' => 'Items',
                        'fields' => [
                            ['name' => 'name', 'placeholder' => 'Name'],
                            ['name' => 'title', 'placeholder' => 'Title'],
                            ['name' => 'text', 'placeholder' => 'Testimonial', 'type' => 'textarea'],
                            ['name' => 'photo', 'placeholder' => 'Photo Path', 'type' => 'image'],
                        ],
                    ],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'elements' => [
                    'services.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'services.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'services.description' => ['type' => 'textarea', 'label' => 'Description'],
                    'services.description_secondary' => ['type' => 'textarea', 'label' => 'Secondary Description'],
                    'services.phone' => ['type' => 'text', 'label' => 'Phone Number'],
                    'services.phone_label' => ['type' => 'text', 'label' => 'Phone Label'],
                    'services.items' => [
                        'type' => 'repeatable',
                        'label' => 'Items',
                        'fields' => [
                            ['name' => 'icon', 'placeholder' => 'Icon class'],
                            ['name' => 'title', 'placeholder' => 'Service Title'],
                            ['name' => 'text', 'placeholder' => 'Description', 'type' => 'textarea'],
                            ['name' => 'image', 'placeholder' => 'Image', 'type' => 'image'],
                            ['name' => 'link', 'placeholder' => 'Link URL'],
                        ],
                    ],
                ],
            ],
            'team' => [
                'label' => 'Team',
                'elements' => [
                    'team.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'team.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'team.description' => ['type' => 'textarea', 'label' => 'Description'],
                    'team.members' => [
                        'type' => 'repeatable',
                        'label' => 'Members',
                        'fields' => [
                            ['name' => 'name', 'placeholder' => 'Name'],
                            ['name' => 'title', 'placeholder' => 'Title'],
                            ['name' => 'photo', 'placeholder' => 'Photo Path', 'type' => 'image'],
                            ['name' => 'description', 'placeholder' => 'Description', 'type' => 'textarea'],
                            ['name' => 'social', 'placeholder' => 'Social Links (comma separated)'],
                        ],
                    ],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'elements' => [
                    'contact.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'contact.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'contact.map' => ['type' => 'textarea', 'label' => 'Map Embed'],
                ],
            ],
            'newsletter' => [
                'label' => 'Newsletter',
                'elements' => [
                    'newsletter.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'newsletter.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'newsletter.description' => ['type' => 'textarea', 'label' => 'Description'],
                    'newsletter.placeholder' => ['type' => 'text', 'label' => 'Input Placeholder'],
                    'newsletter.image' => ['type' => 'image', 'label' => 'Illustration'],
                ],
            ],
        ],
    ],
    'contact' => [
        'sections' => [
            'hero' => [
                'label' => 'Header',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Gunakan Overlay Gelap'],
                    'hero.background' => ['type' => 'image', 'label' => 'Gambar Latar'],
                    'hero.heading' => ['type' => 'text', 'label' => 'Judul'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Deskripsi'],
                ],
            ],
            'details' => [
                'label' => 'Informasi Kontak',
                'elements' => [
                    'details.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'details.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'details.description' => ['type' => 'textarea', 'label' => 'Deskripsi Singkat'],
                    'details.items' => [
                        'type' => 'repeatable',
                        'label' => 'Daftar Kontak',
                        'fields' => [
                            ['name' => 'icon', 'placeholder' => 'Kelas Ikon (contoh: fa-solid fa-phone)'],
                            ['name' => 'label', 'placeholder' => 'Judul Kartu (contoh: Telepon)'],
                            ['name' => 'value', 'placeholder' => 'Isi Informasi'],
                            ['name' => 'link', 'placeholder' => 'Tautan Opsional'],
                        ],
                    ],
                ],
            ],
            'social' => [
                'label' => 'Media Sosial',
                'elements' => [
                    'social.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'social.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'social.description' => ['type' => 'textarea', 'label' => 'Deskripsi Singkat'],
                    'social.items' => [
                        'type' => 'repeatable',
                        'label' => 'Daftar Media Sosial',
                        'fields' => [
                            [
                                'name' => 'icon',
                                'type' => 'select',
                                'placeholder' => 'Pilih ikon media sosial',
                                'options' => [
                                    ['value' => 'fab fa-facebook-f', 'label' => 'Facebook'],
                                    ['value' => 'fab fa-instagram', 'label' => 'Instagram'],
                                    ['value' => 'fab fa-twitter', 'label' => 'Twitter / X'],
                                    ['value' => 'fab fa-whatsapp', 'label' => 'WhatsApp'],
                                    ['value' => 'fab fa-youtube', 'label' => 'YouTube'],
                                    ['value' => 'fab fa-tiktok', 'label' => 'TikTok'],
                                    ['value' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
                                    ['value' => 'fab fa-telegram-plane', 'label' => 'Telegram'],
                                    ['value' => 'fab fa-facebook-messenger', 'label' => 'Messenger'],
                                    ['value' => 'fab fa-line', 'label' => 'LINE'],
                                    ['value' => 'fab fa-pinterest', 'label' => 'Pinterest'],
                                    ['value' => 'fab fa-github', 'label' => 'GitHub'],
                                    ['value' => 'fab fa-dribbble', 'label' => 'Dribbble'],
                                    ['value' => 'fab fa-behance', 'label' => 'Behance'],
                                    ['value' => 'fab fa-medium-m', 'label' => 'Medium'],
                                    ['value' => 'fa fa-link', 'label' => 'Link Default'],
                                ],
                            ],
                            ['name' => 'label', 'placeholder' => 'Nama Media Sosial'],
                            ['name' => 'url', 'placeholder' => 'Tautan'],
                            ['name' => 'visible', 'placeholder' => 'Visibilitas (1 untuk tampil, 0 untuk sembunyi)'],
                        ],
                    ],
                ],
            ],
            'map' => [
                'label' => 'Peta',
                'elements' => [
                    'map.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'map.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'map.embed' => ['type' => 'textarea', 'label' => 'Embed Map (iframe atau URL)'],
                ],
            ],
        ],
    ],
    'product' => [
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Use Dark Overlay'],
                    'hero.image' => ['type' => 'image', 'label' => 'Background Image'],
                    'title' => ['type' => 'text', 'label' => 'Title'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Description'],
                ],
            ],
            'filters' => [
                'label' => 'Filters',
                'elements' => [
                    'filters.search_label' => ['type' => 'text', 'label' => 'Search Label'],
                    'filters.search_placeholder' => ['type' => 'text', 'label' => 'Search Placeholder'],
                    'filters.category_label' => ['type' => 'text', 'label' => 'Category Label'],
                    'filters.category_all_label' => ['type' => 'text', 'label' => 'All Categories Label'],
                    'filters.sort_label' => ['type' => 'text', 'label' => 'Sort Label'],
                    'filters.sort_default_label' => ['type' => 'text', 'label' => 'Default Sort Label'],
                    'filters.sort_price_low_label' => ['type' => 'text', 'label' => 'Price Asc Label'],
                    'filters.sort_price_high_label' => ['type' => 'text', 'label' => 'Price Desc Label'],
                    'filters.sort_popular_label' => ['type' => 'text', 'label' => 'Popular Sort Label'],
                    'filters.apply_label' => ['type' => 'text', 'label' => 'Apply Button Label'],
                    'filters.reset_label' => ['type' => 'text', 'label' => 'Reset Button Label'],
                ],
            ],
            'grid' => [
                'label' => 'Grid',
                'elements' => [
                    'grid.button_label' => ['type' => 'text', 'label' => 'Detail Button Label'],
                    'grid.empty_text' => ['type' => 'textarea', 'label' => 'Empty State Text'],
                ],
            ],
        ],
    ],
    'product-detail' => [
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Use Dark Overlay'],
                    'hero.image' => ['type' => 'image', 'label' => 'Background Image'],
                    'hero.title' => ['type' => 'text', 'label' => 'Breadcrumb Title'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Description'],
                ],
            ],
            'details' => [
                'label' => 'Details',
                'elements' => [
                    'details.quantity_label' => ['type' => 'text', 'label' => 'Quantity Label'],
                    'details.add_to_cart_label' => ['type' => 'text', 'label' => 'Add To Cart Label'],
                    'details.whatsapp_number' => ['type' => 'text', 'label' => 'WhatsApp Number'],
                    'details.added_feedback' => ['type' => 'textarea', 'label' => 'Success Feedback Message'],
                    'details.error_feedback' => ['type' => 'textarea', 'label' => 'Error Feedback Message'],
                ],
            ],
            'comments' => [
                'label' => 'Comments',
                'elements' => [
                    'comments.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'comments.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'comments.empty_text' => ['type' => 'textarea', 'label' => 'Empty State Text'],
                ],
            ],
            'recommendations' => [
                'label' => 'Recommendations',
                'elements' => [
                    'recommendations.visible' => ['type' => 'checkbox', 'label' => 'Show Section'],
                    'recommendations.heading' => ['type' => 'text', 'label' => 'Heading'],
                    'recommendations.button_label' => ['type' => 'text', 'label' => 'Detail Button Label'],
                    'recommendations.empty_text' => ['type' => 'textarea', 'label' => 'Empty State Text'],
                ],
            ],
        ],
    ],
    'article' => [
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Gunakan Overlay Gelap'],
                    'hero.image' => ['type' => 'image', 'label' => 'Gambar Latar'],
                    'hero.heading' => ['type' => 'text', 'label' => 'Judul Halaman'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Deskripsi Singkat'],
                    'search.placeholder' => ['type' => 'text', 'label' => 'Placeholder Pencarian'],
                ],
            ],
            'list' => [
                'label' => 'Daftar Artikel',
                'elements' => [
                    'list.button_label' => ['type' => 'text', 'label' => 'Label Tombol Baca'],
                    'list.empty_text' => ['type' => 'textarea', 'label' => 'Teks Ketika Kosong'],
                    'articles.items' => [
                        'type' => 'repeatable',
                        'label' => 'Artikel',
                        'fields' => [
                            ['name' => 'title', 'placeholder' => 'Judul Artikel'],
                            ['name' => 'slug', 'placeholder' => 'Slug (misal: tips-merawat-tanaman)'],
                            ['name' => 'author', 'placeholder' => 'Penulis'],
                            ['name' => 'date', 'placeholder' => 'Tanggal (YYYY-MM-DD)'],
                            ['name' => 'image', 'placeholder' => 'Path Gambar (opsional)'],
                            ['name' => 'excerpt', 'placeholder' => 'Ringkasan Artikel', 'type' => 'textarea'],
                            ['name' => 'content', 'placeholder' => 'Konten Lengkap', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],
            'timeline' => [
                'label' => 'Arsip Artikel',
                'elements' => [
                    'timeline.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Arsip'],
                    'timeline.heading' => ['type' => 'text', 'label' => 'Judul Arsip'],
                ],
            ],
        ],
    ],
    'article-detail' => [
        'sections' => [
            'hero' => [
                'label' => 'Header',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Gunakan Masker Gelap'],
                    'hero.image' => ['type' => 'image', 'label' => 'Gambar Latar'],
                    'hero.title' => ['type' => 'text', 'label' => 'Judul Breadcrumb'],
                ],
            ],
            'meta' => [
                'label' => 'Informasi Meta',
                'elements' => [
                    'meta.show_author' => ['type' => 'checkbox', 'label' => 'Tampilkan Penulis'],
                    'meta.show_date' => ['type' => 'checkbox', 'label' => 'Tampilkan Tanggal'],
                ],
            ],
            'comments' => [
                'label' => 'Komentar',
                'elements' => [
                    'comments.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Komentar'],
                    'comments.heading' => ['type' => 'text', 'label' => 'Judul Seksi Komentar'],
                    'comments.disabled_text' => ['type' => 'textarea', 'label' => 'Pesan Saat Komentar Dimatikan'],
                ],
            ],
            'recommendations' => [
                'label' => 'Rekomendasi Artikel',
                'elements' => [
                    'recommendations.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'recommendations.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                ],
            ],
        ],
    ],
    'shipping' => [
        'sections' => [
            'form' => [
                'label' => 'Form Pengiriman',
                'elements' => [
                    'form.heading' => ['type' => 'text', 'label' => 'Judul Form'],
                    'form.button_label' => ['type' => 'text', 'label' => 'Label Tombol Lanjut'],
                ],
            ],
            'methods' => [
                'label' => 'Metode Pengiriman',
                'elements' => [
                    'methods.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'methods.fetch_label' => ['type' => 'text', 'label' => 'Label Tombol Cek Ongkir'],
                ],
            ],
            'summary' => [
                'label' => 'Ringkasan Pesanan',
                'elements' => [
                    'summary.heading' => ['type' => 'text', 'label' => 'Judul Ringkasan'],
                    'summary.note' => ['type' => 'textarea', 'label' => 'Catatan Tambahan'],
                ],
            ],
        ],
    ],
    'gallery' => [
        'sections' => [
            'hero' => [
                'label' => 'Header',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Gunakan Overlay Gelap'],
                    'hero.background' => ['type' => 'image', 'label' => 'Gambar Latar'],
                    'hero.heading' => ['type' => 'text', 'label' => 'Judul'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Deskripsi'],
                ],
            ],
            'filters' => [
                'label' => 'Filter Kategori',
                'elements' => [
                    'filters.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Filter'],
                    'filters.heading' => ['type' => 'text', 'label' => 'Judul Filter'],
                    'filters.all_label' => ['type' => 'text', 'label' => 'Label Semua Kategori'],
                ],
            ],
            'grid' => [
                'label' => 'Galeri',
                'elements' => [
                    'grid.heading' => ['type' => 'text', 'label' => 'Judul Galeri'],
                    'grid.empty_text' => ['type' => 'textarea', 'label' => 'Teks Ketika Kosong'],
                ],
            ],
        ],
    ],
    'about' => [
        'sections' => [
            'hero' => [
                'label' => 'Header',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Gunakan Masker Gelap'],
                    'hero.background' => ['type' => 'image', 'label' => 'Gambar Latar'],
                    'hero.heading' => ['type' => 'text', 'label' => 'Judul'],
                    'hero.text' => ['type' => 'textarea', 'label' => 'Deskripsi Singkat'],
                    'hero.breadcrumb' => [
                        'type' => 'repeatable',
                        'label' => 'Breadcrumb',
                        'fields' => [
                            ['name' => 'label', 'placeholder' => 'Nama Breadcrumb'],
                            ['name' => 'link', 'placeholder' => 'Tautan (opsional)'],
                        ],
                    ],
                ],
            ],
            'intro' => [
                'label' => 'Tentang Kami',
                'elements' => [
                    'intro.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'intro.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'intro.description' => ['type' => 'textarea', 'label' => 'Deskripsi'],
                    'intro.description_primary' => ['type' => 'textarea', 'label' => 'Deskripsi Utama'],
                    'intro.description_secondary' => ['type' => 'textarea', 'label' => 'Deskripsi Tambahan'],
                    'intro.image' => ['type' => 'image', 'label' => 'Gambar'],
                    'intro.image_primary' => ['type' => 'image', 'label' => 'Gambar Utama'],
                    'intro.image_secondary' => ['type' => 'image', 'label' => 'Gambar Kedua'],
                    'intro.badge_text' => ['type' => 'text', 'label' => 'Teks Badge'],
                    'intro.checklist' => [
                        'type' => 'repeatable',
                        'label' => 'Checklist',
                        'fields' => [
                            ['name' => 'text', 'placeholder' => 'Teks checklist'],
                        ],
                    ],
                    'intro.button_label' => ['type' => 'text', 'label' => 'Label Tombol'],
                    'intro.button_link' => ['type' => 'text', 'label' => 'Tautan Tombol'],
                    'intro.social_links' => ['type' => 'textarea', 'label' => 'Tautan Sosial (pisahkan dengan koma)'],
                ],
            ],
            'quote' => [
                'label' => 'Quote',
                'elements' => [
                    'quote.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'quote.text' => ['type' => 'textarea', 'label' => 'Teks Quote'],
                    'quote.author' => ['type' => 'text', 'label' => 'Nama Pengutip'],
                ],
            ],
            'team' => [
                'label' => 'Tim Kami',
                'elements' => [
                    'team.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'team.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'team.description' => ['type' => 'textarea', 'label' => 'Deskripsi Pendek'],
                    'team.members' => [
                        'type' => 'repeatable',
                        'label' => 'Anggota Tim',
                        'fields' => [
                            ['name' => 'name', 'placeholder' => 'Nama'],
                            ['name' => 'title', 'placeholder' => 'Jabatan'],
                            ['name' => 'photo', 'placeholder' => 'Path Foto'],
                            ['name' => 'description', 'placeholder' => 'Deskripsi', 'type' => 'textarea'],
                            ['name' => 'social', 'placeholder' => 'Tautan Sosial (pisahkan dengan koma)'],
                        ],
                    ],
                ],
            ],
            'advantages' => [
                'label' => 'Keunggulan Kami',
                'elements' => [
                    'advantages.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'advantages.heading' => ['type' => 'text', 'label' => 'Judul Seksi'],
                    'advantages.description' => ['type' => 'textarea', 'label' => 'Deskripsi Pendek'],
                    'advantages.items' => [
                        'type' => 'repeatable',
                        'label' => 'Daftar Keunggulan',
                        'fields' => [
                            ['name' => 'icon', 'placeholder' => 'Kelas Ikon (contoh: fa fa-leaf)'],
                            ['name' => 'title', 'placeholder' => 'Judul Keunggulan'],
                            ['name' => 'text', 'placeholder' => 'Deskripsi', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'cart' => [
        'sections' => [
            'header' => [
                'label' => 'Header',
                'elements' => [
                    'title' => ['type' => 'text', 'label' => 'Judul Halaman'],
                    'subtitle' => ['type' => 'textarea', 'label' => 'Deskripsi Singkat'],
                ],
            ],
            'empty' => [
                'label' => 'Keranjang Kosong',
                'elements' => [
                    'empty.message' => ['type' => 'textarea', 'label' => 'Pesan Keranjang Kosong'],
                    'empty.button' => ['type' => 'text', 'label' => 'Label Tombol Belanja'],
                ],
            ],
            'actions' => [
                'label' => 'Tombol Aksi',
                'elements' => [
                    'button.shipping' => ['type' => 'text', 'label' => 'Label Tombol Pengiriman'],
                    'button.payment' => ['type' => 'text', 'label' => 'Label Tombol Pembayaran'],
                ],
            ],
        ],
    ],
    'order' => [
        'sections' => [
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    'hero.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Seksi'],
                    'hero.mask' => ['type' => 'checkbox', 'label' => 'Gunakan Overlay Gelap'],
                    'hero.background' => ['type' => 'image', 'label' => 'Gambar Latar'],
                    'hero.heading' => ['type' => 'text', 'label' => 'Judul'],
                    'hero.description' => ['type' => 'textarea', 'label' => 'Deskripsi'],
                ],
            ],
            'empty' => [
                'label' => 'Pesanan Kosong',
                'elements' => [
                    'empty.title' => ['type' => 'text', 'label' => 'Judul Pesan'],
                    'empty.description' => ['type' => 'textarea', 'label' => 'Deskripsi Pesan'],
                    'empty.button' => ['type' => 'text', 'label' => 'Label Tombol'],
                ],
            ],
        ],
    ],
    'layout' => [
        'sections' => [
            'appearance' => [
                'label' => 'Tampilan',
                'elements' => [
                    'theme.variation' => [
                        'type' => 'select',
                        'label' => 'Variasi Warna',
                        'options' => '@theme-variations',
                        'default' => '@theme-variation-default',
                    ],
                ],
            ],
            'navigation' => [
                'label' => 'Navigasi',
                'elements' => [
                    'navigation.brand.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Brand'],
                    'navigation.brand.text' => ['type' => 'text', 'label' => 'Nama Brand'],
                    'navigation.brand.logo' => ['type' => 'image', 'label' => 'Logo Brand'],
                    'navigation.brand.icon' => ['type' => 'text', 'label' => 'Kelas Ikon Brand'],
                    'navigation.link.home' => ['type' => 'checkbox', 'label' => 'Tautan Home'],
                    'navigation.link.about' => ['type' => 'checkbox', 'label' => 'Tautan Tentang Kami'],
                    'navigation.link.products' => ['type' => 'checkbox', 'label' => 'Tautan Produk'],
                    'navigation.link.gallery' => ['type' => 'checkbox', 'label' => 'Tautan Galeri'],
                    'navigation.link.contact' => ['type' => 'checkbox', 'label' => 'Tautan Kontak'],
                    'navigation.link.articles' => ['type' => 'checkbox', 'label' => 'Tautan Artikel'],
                    'navigation.link.article-detail' => ['type' => 'checkbox', 'label' => 'Tautan Detail Artikel'],
                    'navigation.link.orders' => ['type' => 'checkbox', 'label' => 'Tautan Pesanan Saya'],
                    'navigation.icon.cart' => ['type' => 'checkbox', 'label' => 'Ikon Keranjang'],
                    'navigation.button.login' => ['type' => 'checkbox', 'label' => 'Tombol Login'],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'elements' => [
                    'footer.hotlinks.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Hot Links'],
                    'footer.address.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Alamat'],
                    'footer.address.text' => ['type' => 'textarea', 'label' => 'Alamat'],
                    'footer.phone.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Nomor Telepon'],
                    'footer.phone.text' => ['type' => 'text', 'label' => 'Nomor Telepon'],
                    'footer.email.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Email'],
                    'footer.email.text' => ['type' => 'text', 'label' => 'Email'],
                    'footer.social.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Link Media Sosial'],
                    'footer.social.text' => ['type' => 'text', 'label' => 'Link Media Sosial'],
                    'footer.schedule.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Jam Operasional'],
                    'footer.schedule.text' => ['type' => 'text', 'label' => 'Jam Operasional'],
                    'footer.copyright' => ['type' => 'text', 'label' => 'Teks Hak Cipta'],
                ],
            ],
            'floating' => [
                'label' => 'Tombol Melayang',
                'elements' => [
                    'floating.visible' => ['type' => 'checkbox', 'label' => 'Tampilkan Tombol'],
                    'floating.buttons' => [
                        'type' => 'repeatable',
                        'label' => 'Daftar Tombol',
                        'fields' => [
                            [
                                'name' => 'type',
                                'placeholder' => 'Pilih Jenis Tombol',
                                'type' => 'select',
                                'options' => [
                                    ['value' => 'whatsapp', 'label' => 'WhatsApp'],
                                    ['value' => 'phone', 'label' => 'Telepon'],
                                ],
                            ],
                            ['name' => 'label', 'placeholder' => 'Label Tombol'],
                            ['name' => 'target', 'placeholder' => 'Nomor Telepon / WhatsApp'],
                            ['name' => 'message', 'placeholder' => 'Pesan WhatsApp (opsional)', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
