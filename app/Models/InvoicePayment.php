<?php

namespace App\Models;

use App\PaymentProvider;
use App\Policies\InvoicePaymentPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('invoice_id', 'user_id', 'provider', 'provider_order_id', 'amount', 'payment_url', 'provider_status', 'provider_response', 'paid_at')]
#[UsePolicy(InvoicePaymentPolicy::class)]
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
