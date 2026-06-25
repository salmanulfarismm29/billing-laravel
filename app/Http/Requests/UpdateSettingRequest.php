<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;

class UpdateSettingRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'ask_customer_details' => ['sometimes', 'boolean'],
            'ask_payment_method' => ['sometimes', 'boolean'],
        ];
    }
}
