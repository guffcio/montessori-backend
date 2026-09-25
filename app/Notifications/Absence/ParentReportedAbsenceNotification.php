<?php

namespace App\Notifications\Absence;

use App\Models\Absence;
use App\Models\ParentUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentReportedAbsenceNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Absence $absence,
        private ParentUser $parent
    ) {}

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

        $parentFirstName = $this->parent->first_name;
        $parentLastName = $this->parent->last_name;

        $childFirstName = $this->absence->child->first_name;
        $childLastName = $this->absence->child->last_name;

        $absent_at = $this->absence->absent_at->format('d.m.Y');

        return (new MailMessage)
            ->subject("Rodzic {$parentFirstName} {$parentLastName} zgłosił nieobecność dziecka {$childFirstName} {$childLastName} w dniu {$absent_at}")
            ->markdown('emails.absence-created-by-parent', [
                'absence' => $this->absence,
                'parentFirstName' => $parentFirstName,
                'parentLastName' => $parentLastName,
                'childFirstName' => $childFirstName,
                'childLastName' => $childLastName,
                'absent_at' => $absent_at,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {

        $parentFirstName = $this->parent->first_name;
        $parentLastName = $this->parent->last_name;

        $childFirstName = $this->absence->child->first_name;
        $childLastName = $this->absence->child->last_name;

        $absent_at = $this->absence->absent_at->format('d.m.Y');

        return [
            'type' => 'absence',
            'content_id' => $this->absence->id,

            'title' => "Rodzic {$parentFirstName} {$parentLastName} zgłosił nieobecność dziecka",
            'subtitle' => "{$childFirstName} {$childLastName} w dniu {$absent_at}",
        ];
    }
}
