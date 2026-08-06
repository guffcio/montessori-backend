<?php

use App\MessageNotificationStatus;
use App\Models\Message;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use Illuminate\Support\Facades\DB;

class CreateMessageAction
{
    public function __construct() {}

    public function exectute(array $data)
    {

        $recipients = $data['recipients'] ?? [];
        $notification_channels = $data['notification_channels'] ?? [];

        unset($data['recipients'], $data['notification_channels']);

        return DB::transaction(function () use ($data, $recipients, $notification_channels) {
            $message = Message::create($data);

            foreach ($recipients as $user_id) {
                $recipient = MessageRecipient::create([
                    'message_id' => $message->id,
                    'user_id' => $user_id,
                ]);

                if (! empty($notification_channels)) {
                    foreach ($notification_channels as $notification_channel) {
                        MessageNotification::create([
                            'message_recipient_id' => $recipient->id,
                            'channel' => $notification_channel,
                            'status' => MessageNotificationStatus::INIT,
                        ]);
                    }
                }
            }

            return $message;
        });

    }
}
