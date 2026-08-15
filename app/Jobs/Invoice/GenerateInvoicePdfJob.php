<?php

namespace App\Jobs\Invoice;

use App\Models\Invoice;
use App\Services\Invoice\InvoicePdfGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Invoice $invoice
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(InvoicePdfGenerator $invoicePdfGenerator): void
    {

        $path = $invoicePdfGenerator->generate($this->invoice);

        $this->invoice->update([
            'pdf_path' => $path,
        ]);

        SendInvoiceReadyNotificationJob::dispatch($this->invoice);
    }
}
