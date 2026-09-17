<?php

use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Models\Invoice;
use App\PaymentProvider;
use App\PaymentStatus;

test('admin can mark as paid invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $invoice->id,
            'payment_method' => InvoicePaymentMethod::CASH->value,
            'payment_status' => InvoicePaymentStatus::PAID->value,
        ],
    ]);
});

test('parent cannot mark as paid invoice', function () {
    $this->actingAsParent();
    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates invoices table', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'payment_method' => InvoicePaymentMethod::CASH,
        'payment_status' => InvoicePaymentStatus::PAID,
    ]);
});

test('adds new payment row', function () {
    $admin = $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('payments', [
        'payable_type' => Invoice::class,
        'payable_id' => $invoice->id,
        'user_id' => $admin->id,
        'provider' => PaymentProvider::MANUAL,
        'amount' => $invoice->total_amount,
        'status' => PaymentStatus::COMPLETED,
    ]);

});

test('allows just strict payment method type', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::ONLINE,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'payment_method',
    ]);

});

test('rejects already paid invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice([
        'payment_status' => InvoicePaymentStatus::PAID,
    ]
    );

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(422);

});
