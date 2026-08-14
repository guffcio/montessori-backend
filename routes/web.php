<?php

use App\Mail\Message\NewMessageMail;
use App\Models\Invoice;
use App\Models\MessageRecipient;
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

Route::get('/test-pdf', function () {

    $invoice = Invoice::factory()
        ->withItems(5)
        ->withParentSnapshots(2)
        ->create();

    return Pdf::view('pdfs.invoice', ['invoice' => $invoice])
        ->format('a4');
});
