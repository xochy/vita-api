<?php

namespace Database\Factories;

use Database\Factories\Traits\HasImageStates;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workout>
 */
class WorkoutFactory extends Factory
{
    use HasImageStates;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => fake()->randomElement(['Free weights', 'Other exercises', 'Machines']),
            'levels' => $this->generateRandomLevels(),
            'name' => fake()->unique()->bothify('Entrenamiento-?###'),
            'performance' => fake()->paragraph(2),
            'comments' => fake()->paragraph(2),
            'corrections' => fake()->paragraph(2),
            'warnings' => fake()->paragraph(2),
        ];
    }

    /**
     * Generate random levels array and return as JSON string.
     */
    private function generateRandomLevels(): string
    {
        $availableLevels = ['beginner', 'intermediate', 'advanced'];

        // Randomly select 1 to 3 levels (no duplicates)
        $numberOfLevels = $this->faker->numberBetween(1, 3);
        $selectedLevels = $this->faker->randomElements($availableLevels, $numberOfLevels);

        return json_encode(array_values($selectedLevels));
    }

    /**
     * Factory state for beginner level only.
     */
    public function beginnerOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'levels' => json_encode(['beginner']),
        ]);
    }

    /**
     * Factory state for all levels.
     */
    public function allLevels(): static
    {
        return $this->state(fn (array $attributes) => [
            'levels' => json_encode(['beginner', 'intermediate', 'advanced']),
        ]);
    }

    /**
     * Factory state for specific levels.
     */
    public function withLevels(array $levels): static
    {
        // Validate that the levels are allowed
        $allowedLevels = ['beginner', 'intermediate', 'advanced'];
        $validLevels = array_intersect($levels, $allowedLevels);
        $uniqueLevels = array_unique($validLevels);

        return $this->state(fn (array $attributes) => [
            'levels' => json_encode(array_values($uniqueLevels)),
        ]);
    }

    /**
     * Factory state for intermediate and advanced only.
     */
    public function advancedLevels(): static
    {
        return $this->state(fn (array $attributes) => [
            'levels' => json_encode(['intermediate', 'advanced']),
        ]);
    }
}
