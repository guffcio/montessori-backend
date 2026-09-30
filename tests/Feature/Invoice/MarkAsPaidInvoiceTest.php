<?php

use App\Events\Payment\PaymentCompleted;
use App\InvoicePaymentMethod;
use App\InvoicePaymentStatus;
use App\Models\Invoice;
use App\Notifications\Invoice\InvoicePaidNotification;
use App\PaymentProvider;
use App\PaymentStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

test('admin can mark as paid invoice', function (): void {
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

test('parent cannot mark as paid invoice', function (): void {
    $this->actingAsParent();
    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function (): void {
    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates invoices table', function (): void {
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

test('adds new payment row', function (): void {
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

test('allows just strict payment method type', function (): void {
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

test('rejects already paid invoice', function (): void {
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

test('dispatch payment created event', function (): void {
    Event::fake();

    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(PaymentCompleted::class);
});

test('sends notifications', function (): void {
    Notification::fake();
    $admin = $this->actingAsAdmin();

    $parent = $this->createParent();
    $child = $this->createChild();

    $child->parents()->sync($parent);
    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-as-paid", [
        'payment_method' => InvoicePaymentMethod::CASH,
    ]);

    $response->assertStatus(200);

    Notification::assertSentTo(
        [$parent->user, $admin],
        InvoicePaidNotification::class,
        function ($notification, $channels) {
            return in_array('mail', $channels)
                && in_array('database', $channels);
        }
    );
});
