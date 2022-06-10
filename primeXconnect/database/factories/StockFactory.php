<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'on_hand' => $this->faker->randomNumber(),
            'taken' => $this->faker->randomNumber(),
            'production_date' => $this->faker->datetimeThisMonth(),
            'product_id' => Product::factory()
        ];
    }
}
