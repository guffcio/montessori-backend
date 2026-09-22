<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentRejected;
use App\Models\Invoice;
use App\Notifications\Invoice\InvoicePaymentRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoicePaymentRejectedNotificationListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentRejected $event): void
    {
        $payment = $event->payment;
        $payable = $payment->payable;

        if (! $payable instanceof Invoice) {
            return;
        }

        $parents = $payable->child
            ->parents()
            ->with('user')
            ->get();

        foreach ($parents as $parent) {
            if (! $parent->user) {
                continue;
            }

            $parent->user->notify(
                new InvoicePaymentRejectedNotification($payable)
            );
        }
    }
}
