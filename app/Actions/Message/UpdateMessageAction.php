<?php

namespace App\Actions\Message;

use App\Jobs\Message\SendMessageNotificationJob;
use App\MessageNotificationStatus;
use App\Models\Message;
use App\Models\MessageNotification;
use App\Models\MessageRecipient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateMessageAction
{
    public function execute(Message $message, array $data): Collection
    {

        $newRecipients = $data['new_recipients'] ?? [];
        $notificationChannels = $data['notification_channels'] ?? [];

        $messageData = Arr::except($data, [
            'new_recipients',
            'notification_channels',
        ]);

        return DB::transaction(function () use ($message, $messageData, $newRecipients, $notificationChannels) {
            $recipients = new Collection;
            $message->update($messageData);

            foreach ($newRecipients as $userId) {
                $recipient = MessageRecipient::firstOrCreate([
                    'message_id' => $message->id,
                    'user_id' => $userId,
                ]);

                $recipients->push($recipient);

                if (! empty($notificationChannels)) {
                    foreach ($notificationChannels as $notificationChannel) {
                        $notification = MessageNotification::firstOrCreate([
                            'message_recipient_id' => $recipient->id,
                            'channel' => $notificationChannel,
                            'status' => MessageNotificationStatus::INIT,
                        ]);

                        SendMessageNotificationJob::dispatch($notification->id);
                    }
                }
            }

            return $recipients;
        });

    }
}
