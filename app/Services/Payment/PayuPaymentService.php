<?php

namespace App\Services\Payment;

use App\Data\Payment\PaymentCreateData;
use App\Data\Payment\PaymentWebhookData;
use App\Exceptions\PaymentCancellationException;
use App\Exceptions\PaymentCreateException;
use App\Interfaces\PaymentServiceInterface;
use App\PaymentProvider;
use App\PayuPaymentStatus;
use OpenPayU_Configuration;
use OpenPayU_Exception;
use OpenPayU_Order;

class PayuPaymentService implements PaymentServiceInterface
{
    public function createPayment(array $order): PaymentCreateData
    {

        $order['notifyUrl'] = route('payments.notify', [
            'provider' => PaymentProvider::PAYU->value,
        ]);
        $order['continueUrl'] = config('app.frontend_url').'/payment/success';

        $order['customerIp'] = request()->ip() ?: '127.0.0.1';
        $order['merchantPosId'] = OpenPayU_Configuration::getOauthClientId() ? OpenPayU_Configuration::getOauthClientId() : OpenPayU_Configuration::getMerchantPosId();
        $order['currencyCode'] = 'PLN';

        try {
            $response = OpenPayU_Order::create($order);
            if ($response->getStatus() !== 'SUCCESS') {
                throw new PaymentCreateException(
                    'PayU returned non-success status.'
                );
            }

            return new PaymentCreateData(
                providerOrderId: $response->getResponse()->orderId,
                paymentUrl: $response->getResponse()->redirectUri
            );
        } catch (OpenPayU_Exception $e) {
            throw new PaymentCreateException(
                'Unable to create payment',
                previous: $e
            );
        }

    }

    public function cancelPayment(string $providerOrderId): void
    {
        try {
            $response = OpenPayU_Order::cancel($providerOrderId);
            if ($response->getStatus() !== 'SUCCESS') {
                throw new PaymentCancellationException(
                    'PayU returned non-success status.'
                );
            }

        } catch (OpenPayU_Exception $e) {
            throw new PaymentCancellationException(
                'Unable to cancel payment.',
                previous: $e
            );
        }
    }

    public function isCompleted(string $status): bool
    {
        return $status === PayuPaymentStatus::COMPLETED;
    }

    public function isCanceled(string $status): bool
    {
        return $status === PayuPaymentStatus::CANCELED;
    }

    public function isRejected(string $status): bool
    {
        return $status === PayuPaymentStatus::REJECTED;
    }

    public function isCancelable(string $status): bool
    {
        return in_array(
            $status,
            [
                PayuPaymentStatus::NEW,
                PayuPaymentStatus::PENDING,
                PayuPaymentStatus::WAITING_FOR_CONFIRMATION,
            ],
            true
        );
    }

    public function handleWebhook(string $payload): PaymentWebhookData
    {
        $data = trim($payload);

        if (empty($data)) {
            throw new OpenPayU_Exception(
                'Empty webhook payload.'
            );
        }

        $result = OpenPayU_Order::consumeNotification(
            $data
        );

        $payuOrder = $result
            ->getResponse()
            ->order;

        $order = OpenPayU_Order::retrieve($payuOrder->orderId);

        if ($order->getStatus() !== 'SUCCESS') {
            throw new OpenPayU_Exception(
                'Unable to retrieve order.'
            );
        }

        return new PaymentWebhookData(
            providerOrderId: $payuOrder->orderId,
            providerStatus: $payuOrder->status,
            providerResponse: json_decode($payload, true)
        );
    }
}
