<?php

test('admin can delete parent', function () {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(204);
});

test('owner cannot delete own account', function () {
    $parent = $this->actingAsParent();

    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('parent cannot delete another parent', function () {
    $this->actingAsParent();

    $parent = $this->createParent();

    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $parent = $this->createParent();
    $response = $this->deleteJson("/api/parents/{$parent->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('parent is soft deleted', function () {
    $this->actingAsAdmin();

    $parent = $this->createParent();

    $this->deleteJson("/api/parents/{$parent->id}");
    $this->assertSoftDeleted($parent);
});
