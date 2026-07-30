<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Child>
 */
class ChildFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $birthDate = fake()->dateTimeBetween('-10 years', '-3 years');

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => $birthDate,
            'zone_id' => Zone::factory(),
            'pesel' => fake()->pesel($birthDate),
            'started_at' => fake()->date(),
        ];
    }
}
