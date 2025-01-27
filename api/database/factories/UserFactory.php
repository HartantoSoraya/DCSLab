<?php

namespace Database\Factories;

use Carbon\Carbon;
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
        return [
            'name' => str_replace('.', '', fake()->username()),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
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

    public function setCreatedAt(?Carbon $date = null)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'created_at' => is_null($date) ? Carbon::now() : $date,
            ];
        });
    }

    public function setUpdatedAt(?Carbon $date = null)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'updated_at' => is_null($date) ? Carbon::now() : $date,
            ];
        });
    }

    public function setNotRequiredResetPassword()
    {
        return $this->state(function (array $attributes) {
            return [
                'password_changed_at' => Carbon::now(),
            ];
        });
    }

    public function setName($name)
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => strtolower(str_replace(' ', '', $name)),
            ];
        });
    }
}
