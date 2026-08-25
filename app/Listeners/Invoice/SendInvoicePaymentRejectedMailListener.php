<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentRejected;
use App\Mail\Invoice\InvoicePaymentRejectedMail;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Mail;

class SendInvoicePaymentRejectedMailListener implements ShouldQueueAfterCommit
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

        Mail::to($payment->user->email)->send(new InvoicePaymentRejectedMail($payment));
    }
}
