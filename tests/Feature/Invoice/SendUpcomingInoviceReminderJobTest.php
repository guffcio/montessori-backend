<?php

use App\InvoicePaymentStatus;
use App\Jobs\Invoice\SendUpcomingInvoiceReminderJob;
use App\Notifications\Invoice\InvoiceDueSoonNotification;
use Illuminate\Support\Facades\Notification;

test('sends reminders when due date is in 3 days', function (): void {
    Notification::fake();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child);

    $this->createInvoice([
        'child_id' => $child->id,
        'due_date' => today()->addDays(3),
    ]);

    $job = new SendUpcomingInvoiceReminderJob;

    $job->handle();

    Notification::assertSentTo(
        $parent->user,
        InvoiceDueSoonNotification::class
    );
});

test('sends reminders when due date is not in 3 days', function (): void {
    Notification::fake();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child);

    $this->createInvoice([
        'child_id' => $child->id,
        'due_date' => today()->addDays(2),
    ]);

    $job = new SendUpcomingInvoiceReminderJob;

    $job->handle();

    Notification::assertNothingSent();
});

test('does not send reminders when invoice payments status is not unpaid', function (): void {
    Notification::fake();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child);

    $this->createInvoice([
        'child_id' => $child->id,
        'due_date' => today()->addDays(3),
        'payment_status' => InvoicePaymentStatus::PAID,
    ]);

    $job = new SendUpcomingInvoiceReminderJob;

    $job->handle();

    Notification::assertNothingSent();
});
