<?php

namespace App\Actions\Invoice;

use App\Data\Invoice\GenerateInvoiceItemsData;
use App\Data\Invoice\ReissueInvoiceData;
use App\Exceptions\Invoice\InvoiceCannotBeReissuedException;
use App\Jobs\Invoice\GenerateInvoicePdfJob;
use App\Models\Child;
use App\Models\Invoice;
use App\Models\ParentUser;
use App\Services\Invoice\InvoiceItemsGenerator;
use Illuminate\Support\Facades\DB;

class ReissueInvoiceAction
{
    public function __construct(
        private InvoiceItemsGenerator $itemsGenerator
    ) {}

    public function execute(Invoice $invoice, ReissueInvoiceData $dto): Invoice
    {
        if (! $invoice->canBeReissued()) {
            throw new InvoiceCannotBeReissuedException;
        }

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

        $invoice = DB::transaction(function () use ($invoice, $dto, $child, $items) {

            $invoice->update([
                'billing_date' => $dto->billingDate->copy()->startOfMonth(),
                'child_id' => $child->id,
                'child_first_name' => $child->first_name,
                'child_last_name' => $child->last_name,
                'child_pesel' => $child->pesel,
            ]);

            $invoice->parentSnapshots()->delete();

            $child->parents
                ->each(function (ParentUser $parent) use ($invoice): void {
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

            $invoice->items()->delete();
            $invoice->items()->createMany($items);

            $invoice->update([
                'total_amount' => $invoice->items()->sum('total_price'),
            ]);

            return $invoice->fresh();
        });

        GenerateInvoicePdfJob::dispatch($invoice);

        return $invoice;
    }
}
