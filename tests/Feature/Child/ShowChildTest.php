<?php

use App\Models\Child;

test('admin can view children', function (): void {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $response = $this->getJson("/api/children/{$child->id}");
    $response->assertStatus(200);

});

test('parent can view own children', function (): void {
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $child->parents()->sync($parent->id);

    $response = $this->getJson("/api/children/{$child->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $child->id,
        ],
    ]);

});

test('parent cannot view another parent children', function (): void {
    $parent = $this->actingAsParent();
    $secondParent = $this->createParent();

    $child = $this->createChild();
    $otherChild = $this->createChild();

    $child->parents()->sync($parent);
    $otherChild->parents()->sync($secondParent);

    $response = $this->getJson("/api/children/{$otherChild->id}");

    $response->assertStatus(403);
});

test('guest receives 401', function (): void {
    $response = $this->getJson('/api/children/1');
    $response->assertStatus(401);
});

test('return 404 for missing children', function (): void {
    $this->actingAsAdmin();

    $missingId = Child::max('id') + 1;

    $response = $this->getJson("/api/children/{$missingId}");
    $response->assertStatus(404);
});
