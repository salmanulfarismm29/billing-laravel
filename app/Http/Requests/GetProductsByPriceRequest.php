<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiFormRequest;

class GetProductsByPriceRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
