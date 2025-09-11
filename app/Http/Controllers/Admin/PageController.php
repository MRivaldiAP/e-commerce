<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function home()
    {
        $sections = [
            'topbar' => [
                'label' => 'Shipping Bar',
                'elements' => [
                    ['type' => 'text', 'label' => 'Text'],
                    ['type' => 'checkbox', 'label' => 'Show Bar'],
                ],
            ],
            'navigation' => [
                'label' => 'Navigation',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Homepage link', 'id' => 'hero'],
                    ['type' => 'checkbox', 'label' => 'Tea Collection link', 'id' => 'products'],
                    ['type' => 'checkbox', 'label' => 'News link', 'id' => 'testimonials'],
                    ['type' => 'checkbox', 'label' => 'Contact Us link', 'id' => 'contact'],
                ],
            ],
            'hero' => [
                'label' => 'Hero',
                'elements' => [
                    ['type' => 'image', 'label' => 'Background Image'],
                    ['type' => 'text', 'label' => 'Tagline'],
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'textarea', 'label' => 'Description'],
                    ['type' => 'text', 'label' => 'Button Label'],
                    ['type' => 'text', 'label' => 'Button Link'],
                ],
            ],
            'about' => [
                'label' => 'About',
                'elements' => [
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'textarea', 'label' => 'Text'],
                ],
            ],
            'products' => [
                'label' => 'Products',
                'elements' => [
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'repeat', 'label' => 'Product cards'],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'elements' => [
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'repeat', 'label' => 'Service items'],
                ],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'elements' => [
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'repeat', 'label' => 'Testimonials list'],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'elements' => [
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'form', 'label' => 'Form fields'],
                ],
            ],
            'map' => [
                'label' => 'Map',
                'elements' => [
                    ['type' => 'text', 'label' => 'Heading'],
                    ['type' => 'map', 'label' => 'Embed code'],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'elements' => [
                    ['type' => 'checkbox', 'label' => 'Privacy Policy link'],
                    ['type' => 'checkbox', 'label' => 'Terms & Conditions link'],
                    ['type' => 'text', 'label' => 'Copyright Text'],
                ],
            ],
        ];

        return view('admin.pages.home', compact('sections'));
    }
}
