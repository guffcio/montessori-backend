<?php

namespace Api\Actions\Payment;

use App\Data\Payment\PaymentWebhookData;
use App\Interfaces\PaymentServiceInterface;
use App\InvoicePaymentStatus;
use App\Mail\InvoicePayment\InvoicePaymentCanceledMail;
use App\Mail\InvoicePayment\InvoicePaymentCompletedMail;
use App\Mail\InvoicePayment\InvoicePaymentRejectedMail;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessPaymentWebhookAction
{
    public function __construct() {}

    public function execute(PaymentWebhookData $data, PaymentServiceInterface $paymentService): void
    {
        $payment = InvoicePayment::query()
            ->with(['invoice', 'user'])
            ->where('provider_order_id', $data->providerOrderId)
            ->firstOrFail();

        if ($paymentService->isCompleted($payment->provider_status)) {
            return;
        }

        $status = $data->providerStatus;

        $payment->provider_status = $status;
        $payment->provider_response = $data->providerResponse;

        if (
            $paymentService->isCanceled($status) ||
            $paymentService->isRejected($status)
        ) {
            $payment->invoice->payment_status = InvoicePaymentStatus::UNPAID;
            $payment->invoice->payment_method = null;
        }

        if ($paymentService->isCompleted($status)) {
            $payment->paid_at = now();
            $payment->invoice->payment_status = InvoicePaymentStatus::PAID;
        }

        DB::transaction(function () use ($payment) {
            $payment->saveOrFail();
            $payment->invoice->saveOrFail();
        });

        if ($paymentService->isCanceled($status)) {
            Mail::to($payment->user->email)->queue(
                new InvoicePaymentCanceledMail($payment)
            );
        } elseif ($paymentService->isRejected($status)) {
            Mail::to($payment->user->email)->queue(
                new InvoicePaymentRejectedMail($payment)
            );
        } elseif ($paymentService->isCompleted($status)) {
            Mail::to($payment->user->email)->queue(
                new InvoicePaymentCompletedMail($payment)
            );
        }
    }
}
