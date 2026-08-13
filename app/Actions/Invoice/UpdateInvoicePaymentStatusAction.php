<?php

namespace App\Actions\Invoice;

use App\Exceptions\Invoice\InvoiceAlreadyPaidException;
use App\Factories\PaymentServiceFactory;
use App\InvoicePaymentStatus;
use App\Models\Invoice;

class UpdateInvoicePaymentStatusAction
{
    public function __construct(
        private PaymentServiceFactory $paymentFactory
    ) {}

    public function execute(Invoice $invoice, InvoicePaymentStatus $newPaymentStatus): Invoice
    {

        if ($invoice->payment_status->isPaid()) {
            throw new InvoiceAlreadyPaidException(
                'Invoice is already paid'
            );
        }

        if ($invoice->payment_status === InvoicePaymentStatus::PENDING) {
            foreach ($invoice->payments as $payment) {
                $service = $this->paymentFactory->make($payment->provider);
                $service->cancelPayment(
                    $payment->provider_order_id
                );
            }
        }

        $invoice->update([
            'payment_status' => $newPaymentStatus,
        ]);

        return $invoice->fresh();
    }
}
