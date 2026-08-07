<?php

use App\Models\Message;
use App\Models\ParentUser;

test('admin can list messages', function () {
    $this->actingAsAdmin();

    Message::factory()->count(10)->create();

    $response = $this->getJson('/api/messages');

    $response->assertStatus(200);
    expect($response['data'])->toHaveLength(10);

});

test('parent can list only own messages', function () {
    $parent = $this->actingAsParent();

    $otherParent = ParentUser::factory()->create();

    $ownMessages = Message::factory()->withRecipients(5, true)->count(3)->create();
    $otherMessages = Message::factory()->withRecipients(5, true)->count(2)->create();

    $ownMessages->each(function (Message $message) use ($parent) {
        $message->recipients()->firstOrFail()->update([
            'user_id' => $parent->user->id,
        ]);
    });

    $otherMessages->each(function (Message $message) use ($otherParent) {
        $message->recipients()->firstOrFail()->update([
            'user_id' => $otherParent->user->id,
        ]);
    });

    $response = $this->getJson('/api/messages');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');

    $responseIds = collect($response->json('data'))->pluck('id');

    expect($responseIds)->toContain(...$ownMessages->pluck('id'))
        ->not->toContain(...$otherMessages->pluck('id'));
});

test('guest receives 401', function () {
    $response = $this->getJson('/api/messages');

    $response->assertStatus(401);
});
