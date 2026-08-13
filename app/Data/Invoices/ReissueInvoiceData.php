<?php

namespace App\Data\Invoice;

use Carbon\Carbon;
use Illuminate\Support\Arr;

final readonly class ReissueInvoiceData
{
    /**
     * @param  InvoiceItemData[]  $items
     * @param  InvoiceItemData[]  $discounts
     */
    public function __construct(
        public int $childId,
        public Carbon $billingDate,
        public int $holidayDaysCount,
        public ?string $monthlyFee,
        public array $items,
        public array $discounts
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            childId: $data['child_id'],
            billingDate: Carbon::parse($data['year_month']),
            holidayDaysCount: $data['holiday_days_count'],
            monthlyFee: $data['monthly_fee'] ?? null,
            items: Arr::map($data['items'], fn ($item) => InvoiceItemData::fromArray($item)),
            discounts: Arr::map($data['discounts'], fn ($discount) => InvoiceItemData::fromArray($discount))
        );
    }
}
