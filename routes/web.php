<?php

use App\Actions\Invoice\CreateInvoiceAction;
use App\Actions\Invoice\PreviewInvoiceAction;
use App\Data\Invoice\CreateInvoiceData;
use App\Mail\Message\NewMessageMail;
use App\Models\Child;
use App\Models\Invoice;
use App\Models\MessageRecipient;
use App\Models\ParentUser;
use App\Services\CalendarService;
use App\Services\Invoice\InvoicePdfGenerator;
use App\Services\MoneyToWordsConverter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/test-mail', function () {
//     $recipient = MessageRecipient::with(['user', 'message'])->first();

//     Mail::to($recipient->user->email)
//         ->send(new NewMessageMail($recipient->message, $recipient->user));
// });

// Route::get('/test-pdf', function (CalendarService $calendarService, MoneyToWordsConverter $moneyToWords) {

//     $invoice = Invoice::factory()
//         ->withItems(5)
//         ->withParentSnapshots(2)
//         ->create()
//         ->load([
//             'items',
//             'parentSnapshots',
//         ]);

//     return Pdf::view('pdfs.invoice', [
//         'invoice' => $invoice,
//         'billingMonthName' => $calendarService->getMonthName($invoice->billing_date),
//         'amountInWords' => $moneyToWords->convertPln($invoice->total_amount),
//         'chargeAndDiscountItems' => $invoice->chargeAndDiscountItems(),
//         'advanceItems' => $invoice->advanceItems(),
//     ])
//         ->format('a4');
// });

Route::get('/test-create-invoice', function (CreateInvoiceAction $action): void {

    $child = Child::factory()->create();

    $parents = ParentUser::factory(2)->create();

    $child->parents()->sync($parents->pluck('id'));

    $items = [
        [
            'name' => 'Test item row',
            'quantity' => 10,
            'price' => 29.99,
        ],
        [
            'name' => 'Test item row 2',
            'quantity' => 5,
            'price' => 49.99,
        ],
        [
            'name' => 'Test item row 3',
            'quantity' => 4,
            'price' => 67.99,
        ],
    ];

    $discounts = [
        [
            'name' => 'Test discount row',
            'quantity' => 10,
            'price' => 14.55,
        ],
        [
            'name' => 'Test discount row 2',
            'quantity' => 3,
            'price' => 12.44,
        ],
        [
            'name' => 'Test discount row 3',
            'quantity' => 1,
            'price' => 33.99,
        ],
    ];

    $dto = CreateInvoiceData::fromArray([
        'child_id' => $child->id,
        'year_month' => '2026-08',
        'holiday_days_count' => 0,
        'items' => $items,
        'discounts' => $discounts,
        'issue_date' => '2026-08-15',
    ]);

    $action->execute($dto);

    dd('success');
});

Route::get('/test-build-invoice', function (PreviewInvoiceAction $action, InvoicePdfGenerator $invoicePdfGenerator): PdfBuilder {

    $child = Child::factory()->create();

    $parents = ParentUser::factory(2)->create();

    $child->parents()->sync($parents->pluck('id'));

    $items = [
        [
            'name' => 'Test item row',
            'quantity' => 10,
            'price' => 29.99,
        ],
        [
            'name' => 'Test item row 2',
            'quantity' => 5,
            'price' => 49.99,
        ],
        [
            'name' => 'Test item row 3',
            'quantity' => 4,
            'price' => 67.99,
        ],
    ];

    $discounts = [
        [
            'name' => 'Test discount row',
            'quantity' => 10,
            'price' => 14.55,
        ],
        [
            'name' => 'Test discount row 2',
            'quantity' => 3,
            'price' => 12.44,
        ],
        [
            'name' => 'Test discount row 3',
            'quantity' => 1,
            'price' => 33.99,
        ],
    ];

    $dto = CreateInvoiceData::fromArray([
        'child_id' => $child->id,
        'year_month' => '2026-08',
        'holiday_days_count' => 0,
        'items' => $items,
        'discounts' => $discounts,
        'issue_date' => '2026-08-15',
    ]);

    $invoice = $action->execute($dto);

    return $invoicePdfGenerator->build($invoice);
});
