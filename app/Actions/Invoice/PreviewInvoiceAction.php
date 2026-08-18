<?php

namespace App\Actions\Invoice;

use App\Data\Invoice\CreateInvoiceData;
use App\Data\Invoice\GenerateInvoiceItemsData;
use App\Factories\InvoicePdfDataFactory;
use App\Models\Child;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceParentSnapshot;
use App\Models\ParentUser;
use App\Services\Invoice\InvoiceItemsGenerator;

class PreviewInvoiceAction
{
    public function __construct(
        private InvoiceItemsGenerator $itemsGenerator,
        private InvoicePdfDataFactory $invoicePdfDataFactory
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

        $invoice = Invoice::make([
            'child_id' => $child->id,
            'invoice_sequence' => 'PREVIEW',
            'invoice_month' => $dto->issueDate->month,
            'invoice_year' => $dto->issueDate->year,
            'billing_date' => $dto->billingDate->copy()->startOfMonth(),
            'issue_date' => $dto->issueDate,
            'due_date' => $dto->issueDate->copy()->addDays(7),
            'child_first_name' => $child->first_name,
            'child_last_name' => $child->last_name,
            'child_pesel' => $child->pesel,
            'total_amount' => 0,
        ]);

        $invoice->setRelation(
            'parentSnapshots',
            $child->parents->map(fn (ParentUser $parent) => new InvoiceParentSnapshot([
                'first_name' => $parent->first_name,
                'last_name' => $parent->last_name,
                'street' => $parent->street,
                'house_number' => $parent->house_number,
                'apartment_number' => $parent->apartment_number,
                'postal_code' => $parent->postal_code,
                'city' => $parent->city,
            ]))
        );

        $invoice->setRelation(
            'items',
            collect($items)->map(fn (array $item) => new InvoiceItem($item))
        );

        $invoice->total_amount = collect($items)->sum('total_price');

        return $invoice;

    }
}
