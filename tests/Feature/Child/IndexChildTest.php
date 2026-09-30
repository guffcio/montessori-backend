<?php

use App\Models\Child;
use App\Models\ParentUser;

test('admin can list children', function (): void {
    $this->actingAsAdmin();

    Child::factory()->count(10)->create();

    $response = $this->getJson('/api/children');

    $response->assertStatus(200);
    expect($response['data'])->toHaveLength(10);

});

test('parent can list only own children', function (): void {
    $parent = $this->actingAsParent();

    $otherParent = ParentUser::factory()->create();

    $ownChildren = Child::factory()->count(3)->create();
    $otherChildren = Child::factory()->count(2)->create();

    $parent->children()->sync($ownChildren->pluck('id'));
    $otherParent->children()->sync($otherChildren->pluck('id'));

    $response = $this->getJson('/api/children');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');

    $responseIds = collect($response->json('data'))->pluck('id');

    expect($responseIds)->toContain(...$ownChildren->pluck('id'))
        ->not->toContain(...$otherChildren->pluck('id'));
});

test('guest receives 401', function (): void {
    $response = $this->getJson('/api/children');

    $response->assertStatus(401);
});
