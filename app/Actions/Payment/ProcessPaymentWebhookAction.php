<?php

namespace App\Actions\Payment;

use App\Data\Payment\PaymentWebhookData;
use App\Events\Payment\PaymentCanceled;
use App\Events\Payment\PaymentCompleted;
use App\Events\Payment\PaymentRejected;
use App\Interfaces\PaymentServiceInterface;
use App\Models\Payment;
use App\PaymentStatus;
use Illuminate\Support\Facades\DB;

class ProcessPaymentWebhookAction
{
    public function __construct() {}

    public function execute(PaymentWebhookData $data, PaymentServiceInterface $paymentService): void
    {
        $payment = Payment::query()
            ->where('provider_order_id', $data->providerOrderId)
            ->firstOrFail();

        if ($paymentService->isCompleted($payment->provider_status)) {
            return;
        }

        DB::transaction(function () use ($paymentService, $data, $payment): void {

            $payment->update([
                'status' => PaymentStatus::PENDING,
                'provider_status' => $data->providerStatus,
                'provider_response' => $data->providerResponse,
            ]);

            if ($paymentService->isCanceled($data->providerStatus)) {
                $payment->update([
                    'status' => PaymentStatus::CANCELED,
                ]);

                PaymentCanceled::dispatch($payment);

                return;
            }

            if ($paymentService->isRejected($data->providerStatus)) {
                $payment->update([
                    'status' => PaymentStatus::REJECTED,
                ]);

                PaymentRejected::dispatch($payment);

                return;
            }

            if ($paymentService->isCompleted($data->providerStatus)) {
                $payment->update([
                    'paid_at' => now(),
                    'status' => PaymentStatus::COMPLETED,
                ]);

                PaymentCompleted::dispatch($payment);

                return;
            }
        });

    }
}
