<?php

use App\Interfaces\PaymentServiceInterface;
use App\PayuPaymentStatus;

class PayuPaymentService implements PaymentServiceInterface
{
    public function createPayment()
    {
        // TODO: CREATE PAYMENT OPEN PAYU SDK
    }

    public function cancelPayment(string $providerOrderId)
    {
        // TODO: CANCEL PAYMENT OPEN PAYU SDK
    }

    public function canBeCancelled(string $status): bool
    {
        return in_array(
            $status,
            [
                PayuPaymentStatus::NEW,
                PayuPaymentStatus::PENDING,
            ],
            true
        );
    }
}
