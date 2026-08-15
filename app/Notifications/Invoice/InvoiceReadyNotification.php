<?php

namespace App\Notifications\Invoice;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReadyNotification extends Notification
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
            ->subject('Nowa faktura z Przedszkola „Zaczarowany Ogród Montessori”')
            ->markdown('emails.invoice-created', ['invoice' => $this->invoice])
            ->attachFromStorageDisk('local', $this->invoice->pdf_path, "Faktura VAT {$this->invoice->number}.pdf", [
                'mime' => 'application/pdf',
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

            'title' => 'Nowa faktura',
            'subtitle' => $this->invoice->number,
        ];
    }
}
