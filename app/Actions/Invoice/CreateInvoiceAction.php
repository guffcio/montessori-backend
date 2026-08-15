<?php

namespace App\Actions\Invoice;

use App\Data\Invoice\CreateInvoiceData;
use App\Data\Invoice\GenerateInvoiceItemsData;
use App\Jobs\Invoice\GenerateInvoicePdfJob;
use App\Models\Child;
use App\Models\Invoice;
use App\Models\ParentUser;
use App\Services\Invoice\InvoiceItemsGenerator;
use App\Services\Invoice\InvoiceSequenceGenerator;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public function __construct(
        private InvoiceSequenceGenerator $sequenceGenerator,
        private InvoiceItemsGenerator $itemsGenerator,
    ) {}

    public function execute(CreateInvoiceData $dto): Invoice
    {
        $child = Child::with(['parents', 'zone'])
            ->findOrFail($dto->childId);

        $items = $this->itemsGenerator->generate(
            $child,
            new GenerateInvoiceItemsData(
                billingDate: $dto->billingDate,
                holidayDaysCount: $dto->holidayDaysCount,
                monthlyFee: $dto->monthlyFee ?? $child->zone->monthly_fee,
                items: $dto->items,
                discounts: $dto->discounts
            )
        );

        $invoice = DB::transaction(function () use ($dto, $child, $items) {

            $invoiceSequence = $this->sequenceGenerator->generate($dto->issueDate);

            $invoice = Invoice::create([
                'child_id' => $child->id,
                'invoice_sequence' => $invoiceSequence->last_sequence,
                'invoice_month' => $invoiceSequence->month,
                'invoice_year' => $invoiceSequence->year,
                'billing_date' => $dto->billingDate->copy()->startOfMonth(),
                'issue_date' => $dto->issueDate,
                'due_date' => $dto->issueDate->copy()->addDays(7),
                'child_first_name' => $child->first_name,
                'child_last_name' => $child->last_name,
                'child_pesel' => $child->pesel,
                'total_amount' => 0,
            ]);

            $child->parents
                ->each(function (ParentUser $parent) use ($invoice) {
                    $invoice->parentSnapshots()->create([
                        'first_name' => $parent->first_name,
                        'last_name' => $parent->last_name,
                        'street' => $parent->street,
                        'house_number' => $parent->house_number,
                        'apartment_number' => $parent->apartment_number,
                        'postal_code' => $parent->postal_code,
                        'city' => $parent->city,
                    ]);
                });

            $invoice->items()->createMany($items);

            $invoice->update([
                'total_amount' => $invoice->items()->sum('total_price'),
            ]);

            return $invoice;
        });

        GenerateInvoicePdfJob::dispatch($invoice);

        return $invoice;
    }
}
