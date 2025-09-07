<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $id = $this->route('product') ?? $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'tags' => 'nullable|string',
            'is_featured' => 'boolean',
            'status' => 'nullable|boolean',
            'brand_id' => 'nullable|exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
