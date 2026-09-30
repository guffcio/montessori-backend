<?php

use App\Data\Payment\PaymentCreateData;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Mail\Invoice\InvoicePaymentCreatedMail;
use App\PaymentProvider;
use App\PaymentStatus;
use App\Services\Payment\PayuPaymentService;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;

test('parent can create PayU own invoice payment', function (): void {
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class, function (MockInterface $mock): void {
        $mock->expects('createPayment')
            ->once()
            ->andReturn(
                new PaymentCreateData(
                    providerOrderId: 'PAYU_ORDER_123',
                    paymentUrl: 'https://payu.test/redirect'
                )
            );
    });

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(200);

});

test('admin cannot create PayU invoice payment', function (): void {
    $this->actingAsAdmin();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class);

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(403);

});

test('guest cannot create PayU invoice payment', function (): void {
    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class);

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(401);

});

test('required fields are validated', function (): void {
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class);

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'provider',
    ]);

});

test('invoice must exist', function (): void {
    $this->actingAsParent();

    $this->mock(PayuPaymentService::class);

    $response = $this->postJson('api/invoices/1/payments', []);

    $response->assertStatus(404);
});

test('creates payment', function (): void {
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class, function (MockInterface $mock): void {
        $mock->expects('createPayment')
            ->once()
            ->andReturn(
                new PaymentCreateData(
                    providerOrderId: 'PAYU_ORDER_123',
                    paymentUrl: 'https://payu.test/redirect'
                )
            );
    });

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('payments', [
        'payable_id' => $invoice->id,
        'payable_type' => 'App\Models\Invoice',
        'user_id' => $parent->user->id,
        'provider' => PaymentProvider::PAYU,
        'provider_order_id' => 'PAYU_ORDER_123',
        'amount' => $invoice->total_amount,
        'payment_url' => 'https://payu.test/redirect',
        'amount' => $invoice->total_amount,
        'status' => PaymentStatus::NEW,
    ]);

});

test('updates invoice', function (): void {
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class, function (MockInterface $mock): void {
        $mock->expects('createPayment')
            ->once()
            ->andReturn(
                new PaymentCreateData(
                    providerOrderId: 'PAYU_ORDER_123',
                    paymentUrl: 'https://payu.test/redirect'
                )
            );
    });

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('invoices', [
        'payment_status' => InvoicePaymentStatus::PENDING,
        'payment_method' => InvoicePaymentMethod::ONLINE,
    ]);

});

test('send invoice payment created mail', function (): void {
    Mail::fake();
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $this->mock(PayuPaymentService::class, function (MockInterface $mock): void {
        $mock->expects('createPayment')
            ->once()
            ->andReturn(
                new PaymentCreateData(
                    providerOrderId: 'PAYU_ORDER_123',
                    paymentUrl: 'https://payu.test/redirect'
                )
            );
    });

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(200);
    Mail::assertSent(InvoicePaymentCreatedMail::class, $parent->user->email);

});
