<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Weight>
 */
class WeightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'weight' => $this->faker->randomFloat(1, 40, 150),
            'record_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
