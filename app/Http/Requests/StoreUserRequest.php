<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;
use App\Models\Shop;

class StoreUserRequest extends ApiFormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'integer', 'in:1,2,3'], // 1=Admin, 2=Manager, 3=Cashier
            'phone' => ['nullable', 'string', 'max:20'],
            'shop_ids' => ['required', 'array'],
            'shop_ids.*' => ['integer', 'exists:shops,id'],
            'is_active' => ['boolean'],
        ];
    }
}
