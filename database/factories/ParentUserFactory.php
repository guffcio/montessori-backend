<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ParentUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'street' => fake()->sentence(1),
            'house_number' => fake()->numerify('##'),
            'apartment_number' => fake()->numerify('##'),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
        ];
    }
}
