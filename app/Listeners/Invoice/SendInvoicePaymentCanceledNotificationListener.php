<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCanceled;
use App\Models\Invoice;
use App\Notifications\Invoice\InvoicePaymentCanceledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoicePaymentCanceledNotificationListener implements ShouldQueue
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
    public function handle(PaymentCanceled $event): void
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
                new InvoicePaymentCanceledNotification($payable)
            );
        }
    }
}
