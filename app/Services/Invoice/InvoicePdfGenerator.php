<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Services\CalendarService;
use App\Support\MoneyToWordsConverter;
use Spatie\LaravelPdf\Facades\Pdf;

class InvoicePdfGenerator
{
    public function __construct(
        private CalendarService $calendarService,
        private MoneyToWordsConverter $moneyToWords
    ) {}

    public function generate(Invoice $invoice): string
    {

        $path = "invoices/{$invoice->invoice_year}/{$invoice->id}.pdf";

        Pdf::view('pdfs.invoice', [
            'invoice' => $invoice,
            'billingMonthName' => $this->calendarService->getMonthName($invoice->billing_date),
            'amountInWords' => $this->moneyToWords->convertPln($invoice->total_amount),
            'chargeAndDiscountItems' => $invoice->chargeAndDiscountItems(),
            'advanceItems' => $invoice->advanceItems(),
        ])
            ->disk('local')
            ->save($path);

        return $path;
    }
}
