<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentRejected;
use App\InvoicePaymentStatus;
use App\Models\Invoice;

class MarkInvoiceAsUnpaidAfterPaymentRejectedListener
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

        $payable->update([
            'payment_status' => InvoicePaymentStatus::UNPAID,
            'payment_method' => null,
        ]);
    }
}
