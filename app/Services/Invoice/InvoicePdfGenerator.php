<?php

namespace App\Services\Invoice;

use App\Factories\InvoicePdfDataFactory;
use App\Models\Invoice;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

use function Spatie\LaravelPdf\Support\pdf;

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

    public function build(Invoice $invoice): PdfBuilder
    {
        return pdf()
            ->view('pdfs.invoice', [
                'data' => $this->invoicePdfDataFactory->fromInvoice($invoice),
            ])
            ->name('invoice-preview.pdf');
    }
}
