<?php

use App\Models\Child;
use App\Models\Invoice;
use App\Models\ParentUser;

test('admin can list invoices', function () {
    $this->actingAsAdmin();

    Invoice::factory()->count(10)->create();

    $response = $this->getJson('/api/invoices');

    $response->assertStatus(200);
    expect($response['data'])->toHaveLength(10);

});

test('parent can list only own invoices', function () {
    $parent = $this->actingAsParent();
    $otherParent = ParentUser::factory()->create();

    $ownChildren = Child::factory()->count(3)->create();
    $otherChildren = Child::factory()->count(2)->create();

    $parent->children()->sync($ownChildren->pluck('id'));
    $otherParent->children()->sync($otherChildren->pluck('id'));

    $ownInvoices = $ownChildren->map(
        fn ($child) => Invoice::factory()->for($child)->create()
    );

    $otherInvoices = $otherChildren->map(
        fn ($child) => Invoice::factory()->for($child)->create()
    );

    $response = $this->getJson('/api/invoices');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');

    $responseIds = collect($response->json('data'))->pluck('id');

    expect($responseIds)->toContain(...$ownInvoices->pluck('id'))
        ->not->toContain(...$otherInvoices->pluck('id'));
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/invoices');

    $response->assertStatus(401);
});
