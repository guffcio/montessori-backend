<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCompleted;
use App\Mail\Invoice\InvoicePaymentCompletedMail;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Mail;

class SendInvoicePaymentCompletedMailListener implements ShouldQueueAfterCommit
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

        Mail::to($payment->user->email)->send(new InvoicePaymentCompletedMail($payment));
    }
}
