<?php

namespace App\Interfaces;

interface PaymentServiceInterface
{
    public function createPayment();

    public function cancelPayment(string $providerOrderId);

    public function canBeCancelled(string $status);
}
