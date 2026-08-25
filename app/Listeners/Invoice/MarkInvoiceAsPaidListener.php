<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCompleted;
use App\InvoicePaymentStatus;
use App\Models\Invoice;

class MarkInvoiceAsPaidListener
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
    public function handle(PaymentCompleted $event): void
    {

        $payment = $event->payment;
        $payable = $payment->payable;

        if (! $payable instanceof Invoice) {
            return;
        }

        $payable->update([
            'payment_status' => InvoicePaymentStatus::PAID,
        ]);
    }
}
