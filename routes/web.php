<?php

use App\Mail\Message\NewMessageMail;
use App\Models\Invoice;
use App\Models\MessageRecipient;
use App\Services\CalendarService;
use App\Support\MoneyToWordsConverter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-mail', function () {
    $recipient = MessageRecipient::with(['user', 'message'])->first();

    Mail::to($recipient->user->email)
        ->send(new NewMessageMail($recipient->message, $recipient->user));
});

Route::get('/test-pdf', function (CalendarService $calendarService, MoneyToWordsConverter $moneyToWords) {

    $invoice = Invoice::factory()
        ->withItems(5)
        ->withParentSnapshots(2)
        ->create()
        ->load([
            'items',
            'parentSnapshots',
        ]);

    return Pdf::view('pdfs.invoice', [
        'invoice' => $invoice,
        'billingMonthName' => $calendarService->getMonthName($invoice->billing_date),
        'amountInWords' => $moneyToWords->convertPln($invoice->total_amount),
        'chargeAndDiscountItems' => $invoice->chargeAndDiscountItems(),
        'advanceItems' => $invoice->advanceItems(),
    ])
        ->format('a4');
});
