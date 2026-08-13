<?php

namespace App\Data\Invoice;

use Carbon\Carbon;

final readonly class GenerateInvoiceItemsData
{
    /**
     * @param  InvoiceItemData[]  $items
     * @param  InvoiceItemData[]  $discounts
     */
    public function __construct(
        public Carbon $billingDate,
        public int $holidayDaysCount,
        public string $monthlyFee,
        public array $items,
        public array $discounts,
    ) {}
}
