<?php

namespace App\Factories;

use App\Data\Invoice\InvoicePdfData;
use App\Models\Invoice;
use App\Services\CalendarService;
use App\Services\MoneyToWordsConverter;

class InvoicePdfDataFactory
{
    public function __construct(
        private CalendarService $calendarService,
        private MoneyToWordsConverter $moneyToWords
    ) {}

    public function fromInvoice(Invoice $invoice): InvoicePdfData
    {
        return new InvoicePdfData(
            invoice: $invoice,
            billingMonthName: $this->calendarService->getMonthName($invoice->billing_date),
            amountInWords: $this->moneyToWords->convertPln($invoice->total_amount),
            chargeAndDiscountItems: $invoice->chargeAndDiscountItems(),
            advanceItems: $invoice->advanceItems(),
        );
    }
}
