<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $price = fake()->randomElement([15_000, 18_000, 20_000, 25_000, 35_000]);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'qty' => $qty,
            'subtotal' => $qty * $price,
        ];
    }
}
