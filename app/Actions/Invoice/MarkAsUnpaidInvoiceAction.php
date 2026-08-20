<?php

namespace App\Actions\Invoice;

use App\Exceptions\CannotMarkOnlinePaymentAsUnpaidException;
use App\Exceptions\InvoiceAlreadyUnpaidException;
use App\Factories\PaymentServiceFactory;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Models\Invoice;

class MarkAsUnpaidInvoiceAction
{
    public function __construct(
        private PaymentServiceFactory $paymentFactory
    ) {}

    public function execute(Invoice $invoice): Invoice
    {

        if ($invoice->payment_status === InvoicePaymentStatus::UNPAID) {
            throw new InvoiceAlreadyUnpaidException;
        }

        if ($invoice->payment_method === InvoicePaymentMethod::ONLINE) {
            throw new CannotMarkOnlinePaymentAsUnpaidException;
        }

        $invoice->update([
            'payment_method' => null,
            'payment_status' => InvoicePaymentStatus::UNPAID,
        ]);

        return $invoice->fresh();
    }
}
