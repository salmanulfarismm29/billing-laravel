<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'name' => fake()->randomElement(['Masala Tea', 'Green Tea', 'Black Coffee', 'Lemon Tea', 'Vada', 'Samosa']),
            'price' => fake()->randomFloat(2, 10, 50),
            'is_active' => true,
        ];
    }
}
