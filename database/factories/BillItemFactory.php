<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillItem>
 */
class BillItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'product_id' => Product::factory(),
            'price_at_time_of_sale' => fake()->randomFloat(2, 10, 50),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
