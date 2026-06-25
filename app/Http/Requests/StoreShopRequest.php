<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;

class StoreShopRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }
}
