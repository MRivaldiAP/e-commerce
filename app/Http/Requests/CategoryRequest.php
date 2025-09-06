<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $id = $this->route('category') ?? $this->route('id');

        return [
            'name' => 'required|string|max:191',
            'slug' => ['nullable','string','max:191', Rule::unique('categories','slug')->ignore($id)],
            'description' => 'nullable|string',
        ];
    }
}
