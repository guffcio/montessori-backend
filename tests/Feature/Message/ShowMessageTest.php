<?php

use App\Models\Message;

test('admin can view message', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();

    $response = $this->getJson("/api/messages/{$message->id}");

    $response->assertStatus(200);

});

test('parent can view own message', function () {
    $parent = $this->actingAsParent();
    $message = $this->createMessage();

    $message->recipients()->firstOrFail()->update([
        'user_id' => $parent->user->id,
    ]);

    $response = $this->getJson("/api/messages/{$message->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $message->id,
        ],
    ]);

});

test('parent cannot view another parent message', function () {
    $parent = $this->actingAsParent();
    $secondParent = $this->createParent();

    $message = $this->createMessage();
    $otherMessage = $this->createMessage();

    $message->recipients()->firstOrFail()->update([
        'user_id' => $parent->user->id,
    ]);

    $otherMessage->recipients()->firstOrFail()->update([
        'user_id' => $secondParent->user->id,
    ]);

    $response = $this->getJson("/api/messages/{$otherMessage->id}");

    $response->assertStatus(403);
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/messages/1');
    $response->assertStatus(401);
});

test('return 404 for missing message', function () {
    $this->actingAsAdmin();

    $missingId = Message::max('id') + 1;

    $response = $this->getJson("/api/messages/{$missingId}");
    $response->assertStatus(404);
});

test('recipient is marked as read', function () {
    $parent = $this->actingAsParent();
    $message = $this->createMessage();

    $recipient = $message->recipients()->firstOrFail();

    $recipient->update([
        'user_id' => $parent->user->id,
    ]);

    expect($recipient->fresh()->read_at)->toBeNull();

    $this->getJson("/api/messages/{$message->id}");

    expect($recipient->fresh()->read_at)->not->toBeNull();
});

test('already read message keeps original read_at', function () {
    $parent = $this->actingAsParent();
    $message = $this->createMessage();

    $recipient = $message->recipients()->firstOrFail();

    $recipient->update([
        'user_id' => $parent->user->id,
    ]);

    $recipient->markAsRead();
    $read_at = $recipient->fresh()->read_at;
    expect($read_at)->not->toBeNull();

    $this->getJson("/api/messages/{$message->id}");

    expect($recipient->fresh()->read_at)->toBe($read_at);
});
