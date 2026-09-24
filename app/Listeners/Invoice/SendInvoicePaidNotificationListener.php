<?php

namespace App\Listeners\Invoice;

use App\Events\Payment\PaymentCompleted;
use App\Models\Invoice;
use App\Models\ParentUser;
use App\Models\User;
use App\Notifications\Invoice\InvoicePaidNotification;
use App\UserRole;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoicePaidNotificationListener implements ShouldQueue
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

        $payable->child
            ->parents()
            ->with('user')
            ->get()
            ->each(function (ParentUser $parent) use ($payable) {
                $parent->user->notify(
                    new InvoicePaidNotification($payable)
                );
            });

        User::query()
            ->where('role', UserRole::ADMIN)
            ->get()
            ->each(function (User $user) use ($payable) {
                $user->notify(
                    new InvoicePaidNotification($payable)
                );
            });
    }
}
