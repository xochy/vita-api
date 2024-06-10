<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Enums\MeasurementSystemEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender  = fake()->randomElement(GenderEnum::getAllValues());
        $system  = fake()->randomElement(MeasurementSystemEnum::getAllValues());

        return [
            'name'              => fake()->name($gender),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'age'               => fake()->numberBetween(10, 80),
            'gender'            => $gender,
            'system'            => $system,

            'weight' => $system === 'metric'
                ? fake()->randomFloat(2, 10, 200)
                : fake()->randomFloat(2, 50, 400),
            'height' => $system === 'metric'
                ? fake()->randomFloat(2, 0.5, 2.5)
                : fake()->randomFloat(2, 1.5, 8.5),

            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
