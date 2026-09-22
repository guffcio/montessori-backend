<?php

namespace App\Actions\Invoice;

use App\Factories\PaymentServiceFactory;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Mail\Invoice\InvoicePaymentCreatedMail;
use App\Models\Invoice;
use App\PaymentProvider;
use App\PaymentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateInvoicePaymentAction
{
    public function __construct(
        private PaymentServiceFactory $paymentFactory,
    ) {}

    public function execute(PaymentProvider $provider, Invoice $invoice): string
    {
        $paymentService = $this->paymentFactory->make($provider);

        $order = [];

        $order['description'] = "Płatność za fakturę {$invoice->number}";
        $order['totalAmount'] = $invoice->total_amount * 100;
        $order['extOrderId'] = (string) Str::uuid();

        $order['products'][0]['name'] = "Faktura VAT nr {$invoice->number}";
        $order['products'][0]['unitPrice'] = $invoice->total_amount * 100;
        $order['products'][0]['quantity'] = 1;

        $user = Auth::user();
        $order['buyer']['email'] = $user->email;
        $order['buyer']['phone'] = $user->phone;
        $order['buyer']['firstName'] = $user->parent->first_name;
        $order['buyer']['lastName'] = $user->parent->last_name;
        $order['buyer']['language'] = 'pl';

        $providerData = $paymentService->createPayment($order);

        $invoicePayment = DB::transaction(function () use ($provider, $invoice, $providerData) {
            $invoicePayment = $invoice->payments()->create([
                'user_id' => Auth::id(),
                'provider' => $provider,
                'provider_order_id' => $providerData->providerOrderId,
                'payment_url' => $providerData->paymentUrl,
                'amount' => $invoice->total_amount,
                'status' => PaymentStatus::NEW,
            ]);

            $invoice->update([
                'payment_status' => InvoicePaymentStatus::PENDING,
                'payment_method' => InvoicePaymentMethod::ONLINE,
            ]);

            return $invoicePayment;
        });

        Mail::to(Auth::user())->send(new InvoicePaymentCreatedMail($invoicePayment));

        return $invoicePayment->payment_url;

    }
}
