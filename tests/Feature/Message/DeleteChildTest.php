<?php

test('admin can delete child', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $response = $this->deleteJson("/api/children/{$child->id}");
    $response->assertStatus(204);
});

test('parent cannot delete own child', function () {
    $parent = $this->actingAsParent();
    $child = $this->createChild();

    $child->parents()->sync($parent->id);

    $response = $this->deleteJson("/api/children/{$child->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('parent cannot delete another parent child', function () {
    $parent = $this->actingAsParent();
    $otherParent = $this->createParent();

    $child = $this->createChild();
    $otherChild = $this->createChild();

    $child->parents()->sync($parent);
    $otherChild->parents()->sync($otherParent);

    $response = $this->deleteJson("/api/children/{$otherChild->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $child = $this->createChild();
    $response = $this->deleteJson("/api/children/{$child->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('child is soft deleted', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $this->deleteJson("/api/children/{$child->id}");
    $this->assertSoftDeleted($child);
});
