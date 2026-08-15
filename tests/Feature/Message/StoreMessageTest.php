<?php

use App\Jobs\Message\SendMessageNotificationJob;
use App\MessageNotificationChannel;
use App\MessageNotificationStatus;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use App\Models\ParentUser;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('admin can create message', function () {
    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'data' => [
            'author_user_id' => $user->id,
            'title' => 'Example message title',
            'content' => 'Example message content',
        ],
    ]);

    $responseRecipientIds = collect(
        $response->json('data.recipients')
    )->pluck('user_id');

    expect($responseRecipientIds)
        ->toEqualCanonicalizing($recipientsIds);
});

test('parent cannot create message', function () {
    $parent = $this->actingAsParent();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $parent->user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(403);
});

test('guest cannot create message', function () {

    $user = User::factory()->create();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(401);
});

test('required fields are validated', function () {
    $this->actingAsAdmin();

    $response = $this->postJson('/api/messages', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'author_user_id',
        'title',
        'content',
        'recipients',
    ]);

});

test('recipient must exist', function () {
    $user = $this->actingAsAdmin();

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => [1, 2, 3, 4],
        'notification_channels' => [],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['recipients.0', 'recipients.1', 'recipients.2', 'recipients.3']);
});

test('notification channel must be valid enum', function () {
    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => ['mail'],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['notification_channels.0']);
});

test('creates message', function () {
    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);
});

test('creates recipients', function () {

    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(201);

    foreach ($recipientsIds as $userId) {
        $this->assertDatabaseHas('message_recipients', [
            'user_id' => $userId,
            'message_id' => $response->json('data.id'),
            'read_at' => null,
        ]);
    }

});

test('creates message_notifications', function () {
    Queue::fake();

    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(201);

    MessageRecipient::where('message_id', $response->json('data.id'))
        ->each(function (MessageRecipient $recipient) {
            $this->assertDatabaseHas('message_notifications', [
                'message_recipient_id' => $recipient->id,
                'channel' => MessageNotificationChannel::EMAIL,
                'status' => MessageNotificationStatus::INIT,
                'sent_at' => null,
            ]);
        });
});

test('does not create message_notifications when channels are omitted', function () {

    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(201);

    foreach ($recipientsIds as $recipientId) {
        $this->assertDatabaseMissing('message_notifications', [
            'message_recipient_id' => $recipientId,
        ]);
    }
});

test('creates database notifications', function () {
    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(201);

    foreach ($recipientsIds as $recipientId) {
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\\Notifications\\Message\\NewMessageNotification',
            'notifiable_id' => $recipientId,
        ]);
    }
});

test('dispatches SendMessageNotificationJob', function () {
    Queue::fake();
    $user = $this->actingAsAdmin();

    $recipients = ParentUser::factory()->count(5)->create();
    $recipientsIds = $recipients->pluck('user_id');

    $response = $this->postJson('/api/messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
        'recipients' => $recipientsIds,
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(201);

    $notifications = MessageNotification::all();

    expect($notifications)->not->toBeEmpty();

    Queue::assertPushed(
        SendMessageNotificationJob::class,
        $notifications->count()
    );

    foreach ($notifications as $notification) {
        Queue::assertPushed(
            SendMessageNotificationJob::class,
            fn (SendMessageNotificationJob $job) => $job->notificationId === $notification->id
        );
    }

});
