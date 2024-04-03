<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workout>
 */
class WorkoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Entrenamiento-?###'),
            'performance' => fake()->paragraph(),
            'comments' => fake()->paragraph(),
            'corrections' => fake()->paragraph(),
            'warnings' => fake()->paragraph(),
        ];
    }
}
