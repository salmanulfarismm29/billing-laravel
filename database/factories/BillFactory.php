<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
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
            'cashier_id' => User::factory(),
            'total' => fake()->randomFloat(2, 20, 500),
            'payment_method' => fake()->randomElement(array_column(PaymentMethod::cases(), 'value')),
            'customer_name' => fake()->optional()->name(),
            'customer_phone' => fake()->optional()->phoneNumber(),
            'qr_code' => null,
        ];
    }
}
