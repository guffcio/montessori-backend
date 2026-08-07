<?php

namespace App\Actions\Message;

use App\MessageNotificationStatus;
use App\Models\Message;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateMessageAction
{
    public function execute(Message $message, array $data): void
    {

        $new_recipients = $data['new_recipients'] ?? [];
        $notificationChannels = $data['notification_channels'] ?? [];

        $messageData = Arr::except($data, [
            'new_recipients',
            'notification_channels',
        ]);

        DB::transaction(function () use ($message, $messageData, $new_recipients, $notificationChannels) {
            $message->update($messageData);

            foreach ($new_recipients as $user_id) {
                $recipient = MessageRecipient::firstOrCreate([
                    'message_id' => $message->id,
                    'user_id' => $user_id,
                ]);

                if (! empty($notificationChannels)) {
                    foreach ($notificationChannels as $notificationChannel) {
                        MessageNotification::firstOrCreate([
                            'message_recipient_id' => $recipient->id,
                            'channel' => $notificationChannel,
                            'status' => MessageNotificationStatus::INIT,
                        ]);
                    }
                }
            }
        });

    }
}
