<?php

namespace App\Models;

use App\PaymentProvider;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable('user_id', 'provider', 'provider_order_id', 'amount', 'payment_url', 'status', 'provider_status', 'provider_response', 'paid_at')]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'provider_response' => 'array',
            'amount' => 'decimal:2',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
