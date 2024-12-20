<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revenue>
 */
class RevenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a date within the year 2024 (January to December)
        $startDate = '2024-01-01';
        $endDate = '2024-12-31';

        return [
            'date' => $this->faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'), // Ensure it is within 2024
            'revenue' => $this->faker->randomFloat(2, 0, 1000),
            'monthly_subscription_fee' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
