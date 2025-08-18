<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'address' => fake()->address(),
            'city' => fake()->city(),
            'contact' => fake()->phoneNumber(),
            'is_main' => fake()->boolean(),
            'remarks' => fake()->sentence(),
        ];
    }

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'remarks' => $str.' '.fake()->sentence(),
            ];
        });
    }
}
