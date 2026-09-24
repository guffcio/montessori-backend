<?php

namespace App\Jobs\Invoice;

use App\InvoicePaymentStatus;
use App\Models\Invoice;
use App\Models\ParentUser;
use App\Notifications\Invoice\OverdueInvoiceNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOverdueInvoiceReminderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Invoice::query()
            ->where('payment_status', InvoicePaymentStatus::UNPAID)
            ->whereDate('due_date', today()->subDays(3))
            ->get()
            ->each(function (Invoice $invoice) {
                $invoice->child
                    ->parents()
                    ->with('user')
                    ->get()
                    ->each(function (ParentUser $parent) use ($invoice) {
                        if (! $parent->user) {
                            return;
                        }

                        $parent->user->notify(
                            new OverdueInvoiceNotification($invoice)
                        );
                    });
            });
    }
}
