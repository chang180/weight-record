<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyLog>
 */
class DailyLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 year', 'now');
        $isWeekend = in_array(Carbon::parse($date)->dayOfWeek, [0, 6]);
        
        return [
            'user_id' => User::factory(),
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'weight' => $this->faker->optional()->randomFloat(1, 40, 150),
            'task_meal' => $this->faker->boolean(70),
            'task_walk' => $this->faker->boolean(70),
            'task_no_snack' => $this->faker->boolean(70),
            'task_sleep' => $isWeekend ? false : $this->faker->boolean(70),
            'task_no_sugar' => $isWeekend ? $this->faker->boolean(70) : false,
            'daily_points' => 0,
            'weekly_points' => 0,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * 所有任務都完成的狀態
     */
    public function allTasksCompleted(): static
    {
        return $this->state(function (array $attributes) {
            $date = Carbon::parse($attributes['date']);
            $isWeekend = in_array($date->dayOfWeek, [0, 6]);
            
            return [
                'task_meal' => true,
                'task_walk' => true,
                'task_no_snack' => true,
                'task_sleep' => !$isWeekend,
                'task_no_sugar' => $isWeekend,
                'daily_points' => 50,
            ];
        });
    }
}
