<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;


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
            'performance' => fake()->paragraph(2),
            'comments' => fake()->paragraph(2),
            'corrections' => fake()->paragraph(2),
            'warnings' => fake()->paragraph(2),
        ];
    }
}
