<?php

use App\PaymentProvider;

test('parent can create PayU own invoice payment', function () {
    $parent = $this->actingAsParent();

    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(200);

});

test('admin cannot create PayU invoice payment', function () {
    $this->actingAsAdmin();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $response = $this->postJson("api/invoices/{$invoice->id}/payments", [
        'provider' => PaymentProvider::PAYU,
    ]);

    $response->assertStatus(403);

});
