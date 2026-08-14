<?php

namespace App\Models;

use App\PaymentProvider;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('invoice_id', 'user_id', 'provider', 'provider_order_id', 'amount', 'payment_url', 'status', 'provider_response', 'paid_at', 'finished_at')]
class InvoicePayment extends Model
{
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'provider_response' => 'array',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
