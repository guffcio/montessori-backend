<?php

namespace App\Http\Controllers;

use Api\Actions\Payment\ProcessPaymentWebhookAction;
use App\Factories\PaymentServiceFactory;
use App\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceFactory $paymentFactory
    ) {}

    public function notify(Request $request, PaymentProvider $provider, ProcessPaymentWebhookAction $action): Response
    {
        $paymentService = $this->paymentFactory
            ->make($provider);

        $data = $paymentService->handleWebhook($request);

        $action->execute($data, $paymentService);

        return response()->noContent();
    }
}
