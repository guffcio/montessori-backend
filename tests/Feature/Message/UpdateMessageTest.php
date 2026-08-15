<?php

use App\Jobs\Message\SendMessageNotificationJob;
use App\MessageNotificationChannel;
use App\MessageNotificationStatus;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use App\Models\ParentUser;
use Illuminate\Support\Facades\Queue;

test('admin can update message', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'id' => $message->id,
            'title' => 'Example message title',
            'content' => 'Example message content',
        ],
    ]);
});

test('parent cannot update message', function () {
    $this->actingAsParent();
    $message = $this->createMessage();

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['This action is unauthorized.']);
});

test('guest receives 401', function () {
    $message = $this->createMessage();

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);

    $response->assertStatus(401);
    $response->assertJsonFragment(['Unauthenticated.']);
});

test('updates messages table', function () {
    $user = $this->actingAsAdmin();

    $message = $this->createMessage();

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);
});

test('admin can update only strict fields', function () {
    $user = $this->actingAsAdmin();

    $message = $this->createMessage([
        'author_user_id' => $user->id,
    ]);

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'author_user_id' => $this->createParent()->user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('messages', [
        'author_user_id' => $user->id,
        'title' => 'Example message title',
        'content' => 'Example message content',
    ]);

});

test('adds new recipients', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipients = ParentUser::factory()->count(5)->create();
    $newRecipientsIds = $recipients->pluck('user_id');

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
        'new_recipients' => $newRecipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(200);

    foreach ($newRecipientsIds as $userId) {
        $this->assertDatabaseHas('message_recipients', [
            'message_id' => $message->id,
            'user_id' => $userId,
            'read_at' => null,
        ]);
    }
});

test('does not duplicate recipient', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipients = ParentUser::factory()->count(5)->create();
    $newRecipientsIds = $recipients->pluck('user_id');

    $this->patchJson("/api/messages/{$message->id}", [
        'title' => $message->title,
        'content' => $message->content,
        'new_recipients' => $newRecipientsIds,
        'notification_channels' => [],
    ]);

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
        'new_recipients' => [$newRecipientsIds[0]],
        'notification_channels' => [],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['new_recipients.0']);
});

test('create message_notifications for new recipients', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipients = ParentUser::factory()->count(5)->create();
    $newRecipientsIds = $recipients->pluck('user_id');

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => $message->title,
        'content' => $message->content,
        'new_recipients' => $newRecipientsIds,
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);

    MessageRecipient::where('message_id', $message->id)
        ->whereIn('user_id', $newRecipientsIds)
        ->each(function (MessageRecipient $recipient) {
            $this->assertDatabaseHas('message_notifications', [
                'message_recipient_id' => $recipient->id,
                'channel' => MessageNotificationChannel::EMAIL,
                'status' => MessageNotificationStatus::INIT,
                'sent_at' => null,
            ]);
        });

});

test('does not duplicate message_notifications', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipientIds = $message->recipients()->pluck('id');

    $newRecipients = ParentUser::factory()->count(5)->create();
    $newRecipientsIds = $newRecipients->pluck('user_id');

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => $message->title,
        'content' => $message->content,
        'new_recipients' => $newRecipientsIds,
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);

    foreach ($recipientIds as $recipientId) {
        expect(
            MessageNotification::where('message_recipient_id', $recipientId)->count()
        )->toBe(1);
    }
});

test('send notification only to new recipients', function () {
    $this->actingAsAdmin();

    $message = $this->createMessage();
    $recipientIds = $message->recipients()->pluck('id');

    $newRecipients = ParentUser::factory()->count(5)->create();
    $newRecipientsIds = $newRecipients->pluck('user_id');

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => $message->title,
        'content' => $message->content,
        'new_recipients' => $newRecipientsIds,
        'notification_channels' => [],
    ]);

    $response->assertStatus(200);

    foreach ($recipientIds as $recipientId) {
        expect(
            MessageNotification::where('message_recipient_id', $recipientId)->count()
        )->toBe(1);
    }

    foreach ($newRecipientsIds as $recipientId) {
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\\Notifications\\Message\\NewMessageNotification',
            'notifiable_id' => $recipientId,
        ]);
    }
});

test('dispatches job', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $message = $this->createMessage();

    $newRecipients = ParentUser::factory()->count(5)->create();
    $newRecipientsIds = $newRecipients->pluck('user_id');

    $response = $this->patchJson("/api/messages/{$message->id}", [
        'title' => 'Example message title',
        'content' => 'Example message content',
        'new_recipients' => $newRecipientsIds,
        'notification_channels' => [MessageNotificationChannel::EMAIL],
    ]);

    $response->assertStatus(200);

    $newNotifications = MessageNotification::query()
        ->whereHas('recipient', function ($query) use ($message, $newRecipientsIds) {
            $query->where('message_id', $message->id)
                ->whereIn('user_id', $newRecipientsIds);
        })
        ->get();

    expect($newNotifications)->toHaveCount(5);

    Queue::assertPushed(
        SendMessageNotificationJob::class,
        $newNotifications->count()
    );

    foreach ($newNotifications as $notification) {
        Queue::assertPushed(
            SendMessageNotificationJob::class,
            fn (SendMessageNotificationJob $job) => $job->notificationId === $notification->id
        );
    }

});
