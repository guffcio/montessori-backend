<?php

namespace Database\Factories;

use App\InvoicePaymentStatus;
use App\Models\Child;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceParentSnapshot;
use App\Models\ParentUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $issueDate = Carbon::parse(fake()->date());

        return [
            'child_id' => Child::factory(),
            'pdf_path' => null,
            'invoice_sequence' => fake()->unique()->numberBetween(1, 999),
            'invoice_month' => $issueDate->month,
            'invoice_year' => $issueDate->year,
            'billing_date' => $issueDate->copy()->startOfMonth(),
            'issue_date' => $issueDate->toDateString(),
            'due_date' => $issueDate->copy()->addDays(7),

            'child_first_name' => null,
            'child_last_name' => null,
            'child_pesel' => null,
            'total_amount' => 0,

            'payment_status' => InvoicePaymentStatus::UNPAID,
            'paid_at' => null,
        ];
    }

    public function withItems(
        int $count = 1
    ): static {
        return $this->afterCreating(function (Invoice $invoice) use ($count) {
            $items = InvoiceItem::factory($count)
                ->for($invoice)
                ->create();

            $invoice->update([
                'total_amount' => $items->sum('total_price'),
            ]);
        });
    }

    public function withParentSnapshots(
        int $count = 1
    ): static {
        return $this->afterCreating(function (Invoice $invoice) use ($count) {

            $parents = ParentUser::factory($count)->create();

            $invoice->child->parents()->sync($parents);

            foreach ($parents as $parent) {
                InvoiceParentSnapshot::factory()
                    ->fromParent($parent)
                    ->for($invoice)
                    ->create();
            }

        });
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Invoice $invoice) {
            if ($invoice->child) {
                $invoice->child_first_name = $invoice->child->first_name;
                $invoice->child_last_name = $invoice->child->last_name;
                $invoice->child_pesel = $invoice->child->pesel;
            }
        });
    }
}
