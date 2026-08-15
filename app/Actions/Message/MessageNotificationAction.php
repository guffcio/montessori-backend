<?php

namespace App\Actions\Message;

use App\Jobs\Message\SendMessageNotificationJob;
use App\MessageNotificationStatus;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;

class MessageNotificationAction
{
    public function execute(array $data, MessageRecipient $recipient): array
    {
        $createdChannels = [];
        $skippedChannels = [];

        foreach ($data['notification_channels'] as $channel) {

            $alreadyNotified = $recipient->notifications()
                ->where('channel', $channel)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($alreadyNotified) {
                $skippedChannels[] = [
                    'channel' => $channel,
                    'message' => ucfirst($channel)
                        .' notification was already sent within the last 24 hours.',
                ];

                continue;
            }

            $notification = MessageNotification::create([
                'message_recipient_id' => $recipient->id,
                'channel' => $channel,
                'status' => MessageNotificationStatus::INIT,
            ]);

            SendMessageNotificationJob::dispatch($notification->id);

            $createdChannels[] = $channel;
        }

        return [
            'created_channels' => $createdChannels,
            'skipped_channels' => $skippedChannels,
        ];
    }
}
