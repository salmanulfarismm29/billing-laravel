<?php

namespace App\Http\Requests;

use App\Enums\UserRole;

use App\Http\Requests\ApiFormRequest;

class SaveBillingCalculatorRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            // An ordered array of up to 10 distinct price values
            'selectedPrices'   => ['required', 'array', 'max:10'],
            'selectedPrices.*' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'selectedPrices.max'    => 'You may only select a maximum of 10 price shortcuts.',
            'selectedPrices.*.numeric' => 'Each price must be a valid number.',
        ];
    }
}
