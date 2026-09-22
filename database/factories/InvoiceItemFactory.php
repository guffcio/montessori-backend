<?php

namespace Database\Factories;

use App\InvoiceItemSource;
use App\InvoiceItemType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $unitPrice = fake()->randomFloat(2, 50, 500);

        $quantity = fake()->numberBetween(1, 20);

        return [
            'invoice_id' => Invoice::factory(),
            'type' => InvoiceItemType::CHARGE,
            'source' => fake()->randomElement(InvoiceItemSource::cases()),
            'name' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ];
    }
}
