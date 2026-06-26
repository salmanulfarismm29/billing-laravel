<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;
use App\Models\Category;

class UpdateProductRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('category_id') && is_string($this->category_id)) {
            $this->merge([
                'category_id' => Category::resolveHashedId($this->category_id)
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'is_active' => ['boolean'],
        ];
    }
}
