<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'customer_name' => fake()->name(),
            'customer_phone' => (string)fake()->randomNumber(9),
            'customer_address' => fake()->address(),
            'date' => now()->toDateString()
        ];
    }
}
