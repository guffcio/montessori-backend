<?php

use App\InvoicePaymentStatus;

test('admin can delete invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");
    $response->assertStatus(204);
});

test('parent cannot delete invoice', function () {
    $this->actingAsParent();
    $invoice = $this->createInvoice();

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $invoice = $this->createInvoice();
    $response = $this->deleteJson("/api/invoices/{$invoice->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('invoice is soft deleted', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(204);
    $this->assertSoftDeleted($invoice);
});

test('deleted invoice returns 404 on show', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $this->deleteJson("/api/invoices/{$invoice->id}");

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(404);
});

test('cannot delete already paid invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice([
        'payment_status' => InvoicePaymentStatus::PAID,
    ]
    );

    $response = $this->deleteJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(422);
});
