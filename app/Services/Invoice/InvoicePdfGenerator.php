<?php

namespace App\Services\Invoice;

use App\Factories\InvoicePdfDataFactory;
use App\Models\Invoice;
use Spatie\LaravelPdf\Facades\Pdf;

class InvoicePdfGenerator
{
    public function __construct(
        private InvoicePdfDataFactory $invoicePdfDataFactory
    ) {}

    public function generate(Invoice $invoice): string
    {

        $path = "invoices/{$invoice->invoice_year}/{$invoice->id}.pdf";

        Pdf::view('pdfs.invoice', [
            'data' => $this->invoicePdfDataFactory->fromInvoice($invoice),
        ])
            ->disk('local')
            ->save($path);

        return $path;
    }
}
