<?php

use App\Models\ParentUser;

test('admin can view parent', function (): void {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $response = $this->getJson("/api/parents/{$parent->id}");
    $response->assertStatus(200);

});
test('owner can view own parent', function (): void {
    $parent = $this->actingAsParent();

    $response = $this->getJson("/api/parents/{$parent->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'user_id' => $parent->user->id,
            'id' => $parent->id,
        ],
    ]);

});
test('parent cannot view another parent', function (): void {
    $this->actingAsParent();
    $secondParent = $this->createParent();

    $response = $this->getJson("/api/parents/{$secondParent->id}");

    $response->assertStatus(403);
});
test('guest receives 401', function (): void {
    $response = $this->getJson('/api/parents/1');
    $response->assertStatus(401);
});
test('return 404 for missing parent', function (): void {
    $this->actingAsAdmin();

    $missingId = ParentUser::max('id') + 1;

    $response = $this->getJson("/api/parents/{$missingId}");
    $response->assertStatus(404);
});
