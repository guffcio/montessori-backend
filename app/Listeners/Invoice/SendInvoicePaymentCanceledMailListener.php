<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCanceled;
use App\Mail\Invoice\InvoicePaymentCanceledMail;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Mail;

class SendInvoicePaymentCanceledMailListener implements ShouldQueueAfterCommit
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

        Mail::to($payment->user->email)->send(new InvoicePaymentCanceledMail($payment));
    }
}
