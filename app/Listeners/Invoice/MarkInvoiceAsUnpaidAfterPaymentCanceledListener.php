<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCanceled;
use App\InvoicePaymentStatus;
use App\Models\Invoice;

class MarkInvoiceAsUnpaidAfterPaymentCanceledListener
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

        $payable->update([
            'payment_status' => InvoicePaymentStatus::UNPAID,
            'payment_method' => null,
        ]);
    }
}
