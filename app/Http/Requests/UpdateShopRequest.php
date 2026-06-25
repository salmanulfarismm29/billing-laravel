<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;

class UpdateShopRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }
}
