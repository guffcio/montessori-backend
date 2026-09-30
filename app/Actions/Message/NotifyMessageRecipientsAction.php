<?php

namespace App\Actions\Message;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Notifications\Message\NewMessageNotification;
use Illuminate\Database\Eloquent\Collection;

class NotifyMessageRecipientsAction
{
    public function execute(Message $message, Collection $recipients): void
    {

        if ($recipients->isEmpty()) {
            return;
        }

        $recipients
            ->loadMissing('user')
            ->where('read_at', null)
            ->each(function (MessageRecipient $recipient) use ($message): void {
                $recipient->user->notify(new NewMessageNotification($message));
            });
    }
}
