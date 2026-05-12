<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<product>
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
            'category_id' => Category::inRandomOrder()->first()->id,
            'name' => fake()->words(2, true),
            'image' => null,
            'price' => fake()->randomElement([15000, 18000, 20000, 25000, 35000]),
            'stock' => fake()->numberBetween(10, 50),
        ];
    }
}
