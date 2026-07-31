<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absence>
 */
class AbsenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'reported_by_user_id' => User::factory(),
            'charge_catering' => fake()->boolean(),
            'absent_at' => fake()->date(),
        ];
    }
}
