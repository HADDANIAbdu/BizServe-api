<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'price' => $this->faker->randomFloat(2, 50, 1000), // 2 decimal places, between 50 and 1000
            'category' => fake()->domainName(), // 2 decimal places, between 50 and 1000
            'duration' => fake()->text(10), // 2 decimal places, between 50 and 1000
            'description' => $this->faker->text(200), // 2 decimal places, between 50 and 1000
        ];
    }
}
