<?php

namespace App\Actions\Message;

use App\Jobs\Message\SendMessageNotificationJob;
use App\MessageNotificationStatus;
use App\Models\Message;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateMessageAction
{
    public function exectute(array $data): Message
    {

        $recipients = $data['recipients'] ?? [];
        $notificationChannels = $data['notification_channels'] ?? [];

        $messageData = Arr::except($data, [
            'recipients',
            'notification_channels',
        ]);

        return DB::transaction(function () use ($messageData, $recipients, $notificationChannels) {
            $message = Message::create($messageData);

            foreach ($recipients as $userId) {
                $recipient = MessageRecipient::create([
                    'message_id' => $message->id,
                    'user_id' => $userId,
                ]);

                if (! empty($notificationChannels)) {
                    foreach ($notificationChannels as $notificationChannel) {
                        $notification = MessageNotification::create([
                            'message_recipient_id' => $recipient->id,
                            'channel' => $notificationChannel,
                            'status' => MessageNotificationStatus::INIT,
                        ]);

                        SendMessageNotificationJob::dispatch($notification->id);
                    }
                }
            }

            return $message;
        });

    }
}
