<?php

namespace App\Models;

use App\InvoiceItemSource;
use App\InvoiceItemType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('invoice_id', 'type', 'source', 'name', 'quantity', 'unit_price', 'total_price')]
class InvoiceItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => InvoiceItemType::class,
            'source' => InvoiceItemSource::class,
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
