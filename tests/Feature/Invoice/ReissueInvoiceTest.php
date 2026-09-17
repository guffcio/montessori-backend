<?php

use App\InvoiceItemSource;
use App\InvoiceItemType;
use App\InvoicePaymentStatus;
use App\Jobs\Invoice\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Notifications\Invoice\InvoiceReadyNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('admin can reissue invoice', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'child_id' => $child->id,
            'child_first_name' => $child->first_name,
            'child_last_name' => $child->last_name,
            'child_pesel' => $child->pesel,
            'billing_date' => '2026-09-01',
        ],
    ]);
});

test('parent cannot reissue invoice', function () {
    $this->actingAsParent();

    $child = $this->createChild();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(403);
});

test('guest cannot reissue invoice', function () {

    $child = $this->createChild();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(401);
});

test('required fields are validated', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'child_id',
        'year_month',
        'holiday_days_count',
        'items',
    ]);

});

test('child must exist', function () {
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => 1,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['child_id']);
});

test('reissues invoice', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();
    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('invoices', [
        'child_id' => $child->id,
        'billing_date' => '2026-09-01',
        'child_first_name' => $child->first_name,
        'child_last_name' => $child->last_name,
        'child_pesel' => $child->pesel,
        'total_amount' => 1180.77,
        'deleted_at' => null,
    ]);

});

test('creates items', function () {

    $this->actingAsAdmin();

    $child = $this->createChild();
    $invoice = $this->createInvoice();
    $invoice->delete();

    $items = [
        [
            'name' => 'Item 1',
            'quantity' => 1,
            'price' => 29.99,
        ],
        [
            'name' => 'Item 2',
            'quantity' => 2,
            'price' => 39.99,
        ],
        [
            'name' => 'Item 3',
            'quantity' => 3,
            'price' => 49.99,
        ],
    ];

    $discountItems = [
        [
            'name' => 'Discount 1',
            'quantity' => 1,
            'price' => 4.99,
        ],
        [
            'name' => 'Discount 2',
            'quantity' => 1,
            'price' => 9.99,
        ],
    ];

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => $items,
        'discounts' => $discountItems,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseCount('invoice_items', 7);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $response->json('data.id'),
        'type' => InvoiceItemType::CHARGE,
        'source' => InvoiceItemSource::SYSTEM,
        'name' => "Opłata stała (\"Czesne\" {$child->zone->name}) za miesiąc Wrzesień 2026",
        'quantity' => 1,
        'unit_price' => 499.99,
        'total_price' => 499.99,
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'type' => InvoiceItemType::CHARGE,
        'source' => InvoiceItemSource::SYSTEM,
        'name' => "Wyżywienie dziecka {$child->first_name} za miesiąc Wrzesień 2026",
        'quantity' => 21,
        'unit_price' => 30.99,
        'total_price' => 650.79,
    ]);

    foreach ($items as $item) {
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $response->json('data.id'),
            'type' => InvoiceItemType::CHARGE,
            'source' => InvoiceItemSource::MANUAL,
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'total_price' => round($item['price'] * $item['quantity'], 2),
        ]);
    }

    foreach ($discountItems as $discountItem) {
        $unitPrice = -abs($discountItem['price']);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $response->json('data.id'),
            'type' => InvoiceItemType::DISCOUNT,
            'source' => InvoiceItemSource::MANUAL,
            'name' => $discountItem['name'],
            'quantity' => $discountItem['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * $discountItem['quantity'], 2),
        ]);
    }

});

test('creates parent snaphots', function () {

    $this->actingAsAdmin();

    $child = $this->createChild();
    $invoice = $this->createInvoice();
    $invoice->delete();

    $firstParent = $this->createParent();
    $secondParent = $this->createParent();

    $firstParent->children()->sync($child->id);
    $secondParent->children()->sync($child->id);

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseCount('invoice_parent_snapshots', 2);

    $this->assertDatabaseHas('invoice_parent_snapshots', [
        'invoice_id' => $response->json('data.id'),
        'first_name' => $firstParent->first_name,
        'last_name' => $firstParent->last_name,
        'street' => $firstParent->street,
        'house_number' => $firstParent->house_number,
        'apartment_number' => $firstParent->apartment_number,
        'postal_code' => $firstParent->postal_code,
        'city' => $firstParent->city,
    ]);

    $this->assertDatabaseHas('invoice_parent_snapshots', [
        'invoice_id' => $response->json('data.id'),
        'first_name' => $secondParent->first_name,
        'last_name' => $secondParent->last_name,
        'street' => $secondParent->street,
        'house_number' => $secondParent->house_number,
        'apartment_number' => $secondParent->apartment_number,
        'postal_code' => $secondParent->postal_code,
        'city' => $secondParent->city,
    ]);

});

test('dispatch creates pdf', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $child = $this->createChild();
    $invoice = $this->createInvoice();
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(200);

    Queue::assertPushed(GenerateInvoicePdfJob::class);
});

test('generates pdf', function () {

    Storage::fake('local');

    $this->actingAsAdmin();

    $child = $this->createChild();
    $invoice = $this->createInvoice();
    $invoice->delete();

    $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $invoice = Invoice::first();

    expect($invoice->pdf_path)->not->toBeNull();

    $this->assertTrue(
        Storage::disk('local')->exists($invoice->pdf_path)
    );

});

test('sends notifications', function () {
    Notification::fake();
    $this->actingAsAdmin();

    $invoice = $this->createInvoice();
    $invoice->delete();

    $parent = $this->createParent();
    $child = $this->createChild();

    $parent->children()->sync($child->id);

    $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    Notification::assertSentTo(
        $parent->user,
        InvoiceReadyNotification::class,
        function ($notification, $channels) {
            return in_array('mail', $channels)
                && in_array('database', $channels);
        }
    );
});

test('cannot reissue already paid invoice', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $invoice = $this->createInvoice([
        'payment_status' => InvoicePaymentStatus::PAID,
    ]);
    $invoice->delete();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(422);
});

test('cannot reissue not trashed invoice', function () {
    $this->actingAsAdmin();

    $child = $this->createChild();

    $invoice = $this->createInvoice();

    $response = $this->putJson("/api/invoices/{$invoice->id}/reissue", [
        'child_id' => $child->id,
        'year_month' => '2026-09',
        'monthly_fee' => 499.99,
        'holiday_days_count' => 1,
        'items' => [
            [
                'name' => 'Item 1',
                'quantity' => 1,
                'price' => 29.99,
            ],
        ],
        'discounts' => [],
    ]);

    $response->assertStatus(422);
});
