<?php

namespace App\Actions\Invoice;

use App\Exceptions\Invoice\InvoiceAlreadyPaidException;
use App\Factories\PaymentServiceFactory;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Models\Invoice;

class MarkAsPaidInvoiceAction
{
    public function __construct(
        private PaymentServiceFactory $paymentFactory
    ) {}

    public function execute(Invoice $invoice, InvoicePaymentMethod $newPaymentMethod): Invoice
    {

        if ($invoice->payment_status === InvoicePaymentStatus::PAID) {
            throw new InvoiceAlreadyPaidException;
        }

        foreach ($invoice->payments as $payment) {
            $service = $this->paymentFactory->make($payment->provider);

            if (! $service->isCancelable($payment->status)) {
                continue;
            }

            $service->cancelPayment(
                $payment->provider_order_id
            );
        }

        $invoice->update([
            'payment_method' => $newPaymentMethod,
            'payment_status' => InvoicePaymentStatus::PAID,
        ]);

        return $invoice->fresh();
    }
}
