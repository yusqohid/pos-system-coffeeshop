<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-'.fake()->unique()->numerify('########'),
            'status' => 'pending',
            'total_price' => fake()->randomFloat(2, 10_000, 500_000),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
