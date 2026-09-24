<?php

use App\InvoicePaymentStatus;
use App\Jobs\Invoice\SendOverdueInvoiceReminderJob;
use App\Notifications\Invoice\OverdueInvoiceNotification;
use Illuminate\Support\Facades\Notification;

test('sends reminders when due date passed 3 days ago', function () {
    Notification::fake();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child);

    $this->createInvoice([
        'child_id' => $child->id,
        'due_date' => today()->subDays(3),
    ]);

    $job = new SendOverdueInvoiceReminderJob;

    $job->handle();

    Notification::assertSentTo(
        $parent->user,
        OverdueInvoiceNotification::class
    );
});

test('sends reminders when due date is not passed 3 days ago', function () {
    Notification::fake();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child);

    $this->createInvoice([
        'child_id' => $child->id,
        'due_date' => today()->subDays(2),
    ]);

    $job = new SendOverdueInvoiceReminderJob;

    $job->handle();

    Notification::assertNothingSent();
});

test('does not send reminders when invoice payments status is not unpaid', function () {
    Notification::fake();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child);

    $this->createInvoice([
        'child_id' => $child->id,
        'due_date' => today()->subDays(3),
        'payment_status' => InvoicePaymentStatus::PAID,
    ]);

    $job = new SendOverdueInvoiceReminderJob;

    $job->handle();

    Notification::assertNothingSent();
});
