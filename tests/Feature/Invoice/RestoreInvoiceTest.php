<?php

test('admin can restore invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->postJson("/api/invoices/{$invoice->id}/restore");
    $response->assertStatus(200);
});

test('parent cannot restore invoice', function () {
    $this->actingAsParent();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->postJson("/api/invoices/{$invoice->id}/restore");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->postJson("/api/invoices/{$invoice->id}/restore");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('cannot restore not trashed invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->postJson("/api/invoices/{$invoice->id}/restore");

    $response->assertStatus(422);
});
