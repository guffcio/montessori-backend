<?php

namespace App\Notifications\Message;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecipientReadMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Message $message,
        private string $firstName,
        private string $lastName,
        private Carbon $readAt
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rodzic odczytał wiadomość.')
            ->markdown('emails.recipient-read-message', [
                'message' => $this->message,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'readAt' => $this->readAt,
                'url' => $this->message->getFrontendUrl(),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {

        $readAt = $this->readAt->format('d.m.Y H:i:s');

        return [
            'type' => 'message',
            'content_id' => $this->message->id,

            'title' => "Rodzic {$this->firstName} {$this->lastName} odczytał wiadomość",
            'subtitle' => "{$this->message->title}, data odczytu {$readAt}",
        ];
    }
}
