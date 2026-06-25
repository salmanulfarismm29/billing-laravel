<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Http\Requests\ApiFormRequest;

class StoreBillRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true; // Cashiers and Admins can create bills
    }

    protected function prepareForValidation()
    {
        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as &$item) {
                if (isset($item['product_id']) && is_string($item['product_id'])) {
                    $item['product_id'] = Product::resolveHashedId($item['product_id']);
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'integer', 'in:1,2,3'], // 1=Cash, 2=Card, 3=UPI
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
