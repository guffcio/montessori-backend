<?php

namespace App\Actions\Message;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Notifications\Message\NewMessageNotification;
use Illuminate\Database\Eloquent\Collection;

class NotifyMessageRecipientsAction
{
    private function __callStatic($method, $arguments)
    {
        $this->$method($arguments);
    }

    public function execute(Message $message, Collection $recipients): void
    {

        if ($recipients->isEmpty()) {
            return;
        }

        $recipients
            ->loadMissing('user')
            ->where('read_at', null)
            ->each(function (MessageRecipient $recipient) use ($message) {
                $recipient->user->notify(new NewMessageNotification($message));
            });
    }
}
