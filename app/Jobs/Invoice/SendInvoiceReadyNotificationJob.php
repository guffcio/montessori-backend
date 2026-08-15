<?php

namespace App\Jobs\Invoice;

use App\Models\Invoice;
use App\Notifications\Invoice\InvoiceReadyNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInvoiceReadyNotificationJob implements ShouldQueue
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
    public function handle(): void
    {
        $parents = $this->invoice
            ->child
            ->parents()
            ->with('user')
            ->get();

        foreach ($parents as $parent) {
            if (! $parent->user) {
                continue;
            }

            $parent->user->notify(
                new InvoiceReadyNotification($this->invoice)
            );
        }
    }
}
