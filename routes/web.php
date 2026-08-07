<?php

use App\Mail\Message\NewMessageMail;
use App\Models\MessageRecipient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-mail', function () {
    $recipient = MessageRecipient::with(['user', 'message'])->first();

    Mail::to($recipient->user->email)
        ->send(new NewMessageMail($recipient->message, $recipient->user));
});
