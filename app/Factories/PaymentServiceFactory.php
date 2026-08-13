<?php

namespace App\Factories;

use App\Interfaces\PaymentServiceInterface;
use App\PaymentProvider;
use PayuPaymentService;

class PaymentServiceFactory
{
    public function __construct(
        private PayuPaymentService $payuService
    ) {}

    public function make(PaymentProvider $provider): PaymentServiceInterface
    {
        return match ($provider) {
            PaymentProvider::PAYU => $this->payuService
        };
    }
}
