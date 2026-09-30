<?php

namespace App\Actions\Invoice;

use App\Exceptions\CannotMarkOnlinePaymentAsUnpaidException;
use App\Exceptions\InvoiceAlreadyUnpaidException;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\PaymentProvider;
use App\PaymentStatus;
use Illuminate\Support\Facades\DB;

class MarkAsUnpaidInvoiceAction
{
    public function __construct() {}

    public function execute(Invoice $invoice): Invoice
    {

        if ($invoice->payment_status === InvoicePaymentStatus::UNPAID) {
            throw new InvoiceAlreadyUnpaidException;
        }

        if ($invoice->payment_method === InvoicePaymentMethod::ONLINE) {
            throw new CannotMarkOnlinePaymentAsUnpaidException;
        }

        DB::transaction(function () use ($invoice): void {

            $invoice->update([
                'payment_method' => null,
                'payment_status' => InvoicePaymentStatus::UNPAID,
            ]);

            $invoice->payments()
                ->where('provider', PaymentProvider::MANUAL)
                ->each(function (Payment $payment): void {
                    $payment->update([
                        'status' => PaymentStatus::CANCELED,
                    ]);
                });
        });

        return $invoice->fresh();
    }
}
