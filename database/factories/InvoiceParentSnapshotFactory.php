<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceParentSnapshot;
use App\Models\ParentUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceParentSnapshot>
 */
class InvoiceParentSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'street' => fake()->streetName(),
            'house_number' => fake()->buildingNumber(),
            'apartment_number' => fake()->buildingNumber(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
        ];
    }

    public function fromParent(ParentUser $parent): static
    {
        return $this->state([
            'first_name' => $parent->first_name,
            'last_name' => $parent->last_name,
            'street' => $parent->street,
            'house_number' => $parent->house_number,
            'apartment_number' => $parent->apartment_number,
            'postal_code' => $parent->postal_code,
            'city' => $parent->city,
        ]);
    }
}
