<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCanceled;
use App\Models\Invoice;
use App\Models\ParentUser;
use App\Models\User;
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

        $payable->child
            ->parents()
            ->with('user')
            ->get()
            ->each(function (ParentUser $parent) use ($payable) {
                $parent->user->notify(
                    new InvoicePaymentCanceledNotification($payable)
                );
            });

        User::admins()
            ->each(function (User $user) use ($payable) {
                $user->notify(
                    new InvoicePaymentCanceledNotification($payable)
                );
            });
    }
}
