<?php

namespace App\Jobs;

use App\Mail\Message\NewMessageMail;
use App\MessageNotificationChannel;
use App\MessageNotificationStatus;
use App\Models\MessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMessageNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $messageId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $notifications = MessageNotification::query()
            ->whereHas('recipient', fn ($query) => $query->where('message_id', $this->messageId))
            ->where('status', MessageNotificationStatus::INIT)
            ->with(['recipient.user', 'recipient.message'])
            ->get();

        if ($notifications->isEmpty()) {
            return;
        }

        foreach ($notifications as $notification) {
            $notification->update(['status' => MessageNotificationStatus::PENDING]);

            try {
                if ($notification->channel === MessageNotificationChannel::EMAIL) {
                    Mail::to($notification->recipient->user->email)->send(new NewMessageMail($notification->recipient->message));
                }

                $notification->update([
                    'status' => MessageNotificationStatus::SENT,
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $notification->update(['status' => MessageNotificationStatus::FAILED]);

                throw $e;
            }

        }
    }
}
