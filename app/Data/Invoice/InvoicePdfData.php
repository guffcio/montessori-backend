<?php

namespace App\Data\Invoice;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class InvoicePdfData
{
    public function __construct(
        public Invoice $invoice,
        public string $billingMonthName,
        public string $amountInWords,
        public Collection $chargeAndDiscountItems,
        public Collection $advanceItems,
    ) {}

}
