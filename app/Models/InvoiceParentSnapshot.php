<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('invoice_id', 'first_name', 'last_name', 'street', 'house_number', 'apartment_number', 'postal_code', 'city')]
class InvoiceParentSnapshot extends Model
{
    use HasFactory;

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
