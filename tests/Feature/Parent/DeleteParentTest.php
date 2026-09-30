<?php

test('admin can delete parent', function (): void {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(204);
});

test('owner cannot delete own account', function (): void {
    $parent = $this->actingAsParent();

    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('parent cannot delete another parent', function (): void {
    $this->actingAsParent();

    $parent = $this->createParent();

    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function (): void {
    $parent = $this->createParent();
    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('parent is soft deleted', function (): void {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $this->deleteJson("/api/parents/{$parent->id}");
    $this->assertSoftDeleted($parent);
});
