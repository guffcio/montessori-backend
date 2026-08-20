<?php

namespace App\Actions\Invoice;

use App\Exceptions\CannotMarkOnlinePaymentAsUnpaidException;
use App\Exceptions\InvoiceAlreadyUnpaidException;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\PaymentProvider;
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

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'payment_method' => null,
                'payment_status' => InvoicePaymentStatus::UNPAID,
            ]);

            $invoice->payments()
                ->where('provider', PaymentProvider::MANUAL)
                ->each(function (InvoicePayment $invoicePayment) {
                    $invoicePayment->update([
                        'provider_status' => 'CANCELED',
                    ]);
                });
        });

        return $invoice->fresh();
    }
}
