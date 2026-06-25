<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;

class UpdateProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'is_active' => ['boolean'],
        ];
    }
}
