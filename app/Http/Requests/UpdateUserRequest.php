<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Models\User;
use App\Http\Requests\ApiFormRequest;
use App\Models\Shop;

class UpdateUserRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('shop_ids') && is_array($this->shop_ids)) {
            $this->merge([
                'shop_ids' => array_map(fn($id) => is_string($id) ? Shop::resolveHashedId($id) : $id, $this->shop_ids)
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
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . User::resolveHashedId($this->input('hash'))],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'integer', 'in:1,2,3'],
            'phone' => ['nullable', 'string', 'max:20'],
            'shop_ids' => ['sometimes', 'array'],
            'shop_ids.*' => ['integer', 'exists:shops,id'],
            'is_active' => ['boolean'],
        ];
    }
}
