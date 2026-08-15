<?php

namespace App\Models;

use App\InvoiceItemType;
use App\InvoicePaymentStatus;
use App\Policies\InvoicePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable('child_id', 'pdf_path', 'invoice_sequence', 'invoice_month', 'invoice_year', 'billing_date', 'issue_date', 'due_date', 'child_first_name', 'child_last_name', 'child_pesel', 'total_amount', 'payment_status', 'paid_at')]
#[UsePolicy(InvoicePolicy::class)]
class Invoice extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'billing_date' => 'date',
            'issue_date' => 'date',
            'due_date' => 'date',
            'payment_status' => InvoicePaymentStatus::class,
            'total_amount' => 'decimal:2',
        ];
    }

    protected function number(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['invoice_sequence'].'/'.$attributes['invoice_month'].'/'.$attributes['invoice_year']
        );
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

    public function itemsByType(InvoiceItemType $type): Collection
    {
        return $this->items->where('type', $type);
    }

    public function totalByType(InvoiceItemType $type): float
    {
        return $this->itemsByType($type)->sum('total_price');
    }

    public function subtotalBeforeAdvances(): float
    {
        return
            $this->totalByType(InvoiceItemType::CHARGE)
            + $this->totalByType(InvoiceItemType::DISCOUNT);
    }

    public function chargeAndDiscountItems(): Collection
    {
        return $this->itemsByType(InvoiceItemType::CHARGE)
            ->concat($this->itemsByType(InvoiceItemType::DISCOUNT));
    }

    public function advanceItems(): Collection
    {
        return $this->itemsByType(InvoiceItemType::ADVANCE);
    }
}
