<?php

namespace App\Data\Payment;

final readonly class PaymentWebhookData
{
    public function __construct(
        public string $providerOrderId,
        public string $providerStatus,
        public array $providerResponse
    ) {}
}
