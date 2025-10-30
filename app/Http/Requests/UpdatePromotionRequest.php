<?php

namespace App\Http\Requests;

use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in([Promotion::TYPE_PERCENTAGE, Promotion::TYPE_FIXED])],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'audience_type' => ['required', Rule::in([Promotion::AUDIENCE_ALL, Promotion::AUDIENCE_REGISTERED, Promotion::AUDIENCE_SELECTED])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'user_ids' => ['required_if:audience_type,' . Promotion::AUDIENCE_SELECTED, 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('audience_type')) {
            $this->merge(['audience_type' => Promotion::AUDIENCE_ALL]);
        }

        if (! $this->filled('starts_at')) {
            $this->merge(['starts_at' => null]);
        }

        if (! $this->filled('ends_at')) {
            $this->merge(['ends_at' => null]);
        }

        if ($this->input('audience_type') !== Promotion::AUDIENCE_SELECTED) {
            $this->merge(['user_ids' => []]);
        }

        if ($this->input('discount_type') === Promotion::TYPE_PERCENTAGE) {
            $value = (float) $this->input('discount_value');
            if ($value > 100) {
                $this->merge(['discount_value' => 100]);
            }
        }
    }
}
