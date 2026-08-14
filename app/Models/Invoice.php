<?php

namespace App\Models;

use App\InvoicePaymentStatus;
use App\Policies\InvoicePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('child_id', 'invoice_sequence', 'invoice_month', 'invoice_year', 'billing_date', 'issue_date', 'due_date', 'child_first_name', 'child_last_name', 'child_pesel', 'total_amount', 'payment_status', 'paid_at')]
#[UsePolicy(InvoicePolicy::class)]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'billing_date' => 'date',
            'issue_date' => 'date',
            'due_date' => 'date',
            'payment_status' => InvoicePaymentStatus::class,
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function parentSnapshots(): HasMany
    {
        return $this->hasMany(InvoiceParentSnapshot::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function canBeRestored(): bool
    {
        return $this->trashed();
    }

    public function canBeReissued(): bool
    {
        return $this->payment_status->canBeReissued() && $this->trashed();
    }
}
