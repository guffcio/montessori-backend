<?php

use App\Models\Invoice;

test('admin can view invoice', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(200);

});

test('parent can view own invoice', function () {
    $parent = $this->actingAsParent();

    $child = $this->createChild();
    $child->parents()->sync($parent->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $invoice->id,
        ],
    ]);

});

test('parent cannot view another parent invoice', function () {
    $this->actingAsParent();
    $secondParent = $this->createParent();

    $child = $this->createChild();
    $child->parents()->sync($secondParent->id);

    $invoice = $this->createInvoice([
        'child_id' => $child->id,
    ]);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(403);
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/invoices/1');
    $response->assertStatus(401);
});

test('return 404 for missing message', function () {
    $this->actingAsAdmin();

    $missingId = Invoice::max('id') + 1;

    $response = $this->getJson("/api/invoices/{$missingId}");
    $response->assertStatus(404);
});
