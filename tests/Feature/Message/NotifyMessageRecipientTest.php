<?php

use App\Jobs\Message\SendMessageNotificationJob;
use App\MessageNotificationChannel;
use App\MessageNotificationStatus;
use App\Models\MessageNotification;
use Illuminate\Support\Facades\Queue;

test('admin can notify recipient', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $this->travel(25)->hours();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'success' => true,
            'created_channels' => [MessageNotificationChannel::EMAIL->value],
            'skipped_channels' => [],
        ],
    ]);
});

test('parent cannot notify recipient', function () {
    $this->actingAsParent();

    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates message_notifications table', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $this->travel(25)->hours();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $notification = MessageNotification::latest('id')->first();

    $response->assertStatus(200);

    $this->assertDatabaseHas('message_notifications', [
        'id' => $notification->id,
        'message_recipient_id' => $recipient->id,
        'channel' => MessageNotificationChannel::EMAIL,
        'status' => MessageNotificationStatus::INIT,
        'sent_at' => null,
    ]);
});

test('recipient must belong to message', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipient = $this->createParent();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(404);
    expect($response->json('message'))
        ->toContain('No query results for model');

});

test('dispatches send notification job', function () {

    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $beforeCount = MessageNotification::count();

    $this->travel(25)->hours();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);

    expect(MessageNotification::count())
        ->toBe($beforeCount + 1);

    $notification = MessageNotification::latest('id')->first();

    Queue::assertPushed(
        SendMessageNotificationJob::class,
        fn (SendMessageNotificationJob $job) => $job->notificationId === $notification->id
    );
});

test('skips channel notified within last 24 hours', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $beforeCount = MessageNotification::count();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);

    expect(MessageNotification::count())->toBe($beforeCount);

    $response->assertJson([
        'data' => [
            'success' => false,
            'created_channels' => [],
            'skipped_channels' => [
                [
                    'channel' => MessageNotificationChannel::EMAIL->value,
                    'message' => 'Email notification was already sent within the last 24 hours.',
                ],
            ],
        ],
    ]);

    Queue::assertNotPushed(SendMessageNotificationJob::class);
});

test('allows notification after 24 hours', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipient = $message->recipients()->firstOrFail();

    $beforeCount = MessageNotification::count();

    $this->travel(25)->hours();

    $response = $this->postJson("/api/messages/{$message->id}/notify/{$recipient->id}", [
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);

    expect(MessageNotification::count())->toBe($beforeCount + 1);

    $notification = MessageNotification::latest('id')->first();

    $response->assertJson([
        'data' => [
            'success' => true,
            'created_channels' => [MessageNotificationChannel::EMAIL->value],
            'skipped_channels' => [],
        ],
    ]);

    Queue::assertPushed(SendMessageNotificationJob::class, fn (SendMessageNotificationJob $job) => $job->notificationId === $notification->id);
});

// test('creates only channels that are not in cooldown', function () {});

// test('creates notifications for multiple channels', function () {});
