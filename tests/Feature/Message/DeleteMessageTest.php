<?php

test('admin can delete message', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();

    $response = $this->deleteJson("/api/messages/{$message->id}");
    $response->assertStatus(204);
});

test('parent cannot delete message', function () {
    $this->actingAsParent();
    $message = $this->createMessage();

    $response = $this->deleteJson("/api/messages/{$message->id}");
    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $message = $this->createMessage();
    $response = $this->deleteJson("/api/messages/{$message->id}");
    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('message is soft deleted', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();

    $this->deleteJson("/api/messages/{$message->id}");
    $this->assertSoftDeleted($message);
});

test('deleted message returns 404 on show', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();

    $this->deleteJson("/api/messages/{$message->id}");

    $response = $this->getJson("/api/messages/{$message->id}");

    $response->assertStatus(404);
});
