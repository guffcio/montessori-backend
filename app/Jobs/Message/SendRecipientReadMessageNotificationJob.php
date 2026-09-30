<?php

namespace App\Jobs\Message;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use App\Notifications\Message\RecipientReadMessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendRecipientReadMessageNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $messageId,
        private int $recipientId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = Message::findOrFail($this->messageId);
        $recipient = MessageRecipient::query()
            ->with('user.parent')
            ->findOrFail($this->recipientId);

        $parent = $recipient->user->parent;

        User::admins()
            ->each(function (User $admin) use ($message, $parent, $recipient): void {
                $admin->notify(
                    new RecipientReadMessageNotification(
                        message: $message,
                        firstName: $parent->first_name,
                        lastName: $parent->last_name,
                        readAt: $recipient->read_at ?? now()
                    ));
            });
    }
}
