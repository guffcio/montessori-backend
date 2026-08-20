<?php

namespace App\Interfaces;

use App\Data\Payment\PaymentCreateData;
use App\Data\Payment\PaymentWebhookData;

interface PaymentServiceInterface
{
    public function createPayment(array $order): PaymentCreateData;

    public function cancelPayment(string $providerOrderId): void;

    public function isCancelable(string $status): bool;

    public function isCompleted(string $status): bool;

    public function isCanceled(string $status): bool;

    public function isRejected(string $status): bool;

    public function handleWebhook(string $payload): PaymentWebhookData;
}
