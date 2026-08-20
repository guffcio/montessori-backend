<?php

namespace App\Data\Payment;

final readonly class PaymentCreateData
{
    public function __construct(
        public string $providerOrderId,
        public string $redirectUrl
    ) {}
}
