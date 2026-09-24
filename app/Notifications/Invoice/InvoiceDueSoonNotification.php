<?php

namespace App\Notifications\Invoice;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Invoice $invoice
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
            ->subject('Przypomnienie o zbliżającym się terminie płatności')
            ->markdown('emails.invoice-due-soon', [
                'invoice' => $this->invoice,
                'dueDate' => $this->invoice->due_date->format('d.m.Y'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice',
            'content_id' => $this->invoice->id,

            'title' => 'Zbliża się termin płatności za fakturę',
            'subtitle' => "Za {$this->invoice->daysUntilDue()} dni upływa termin płatności za fakturę {$this->invoice->number}.",
        ];
    }
}
