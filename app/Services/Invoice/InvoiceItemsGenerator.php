<?php

namespace App\Services\Invoice;

use App\Data\Invoice\GenerateInvoiceItemsData;
use App\Data\Invoice\InvoiceItemData;
use App\InvoiceItemSource;
use App\InvoiceItemType;
use App\Models\Child;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class InvoiceItemsGenerator
{
    private const LUNCH_COST = '30.99';

    private const ADVANCE_LUNCH_COST = '30.99';

    public function __construct(
        private CalendarService $calendarService
    ) {}

    public function generate(
        Child $child,
        GenerateInvoiceItemsData $dto
    ): array {

        $items = [
            $this->generateFeeItem($child, $dto->billingDate, $dto->monthlyFee),
            $this->generateMealItem($child, $dto->billingDate, $dto->holidayDaysCount),
            ...$this->generateCustomItems($dto->items),
            ...$this->generateDiscountItems($dto->discounts),
        ];

        if ($advanceItem = $this->generateAdvanceMealItem($child, $dto->billingDate)) {
            $items[] = $advanceItem;
        }

        return $items;
    }

    private function makeItem(array $data): array
    {
        return [
            ...$data,
            'total_price' => round($data['unit_price'] * $data['quantity'], 2),
        ];
    }

    private function generateFeeItem(Child $child, Carbon $billingDate, string $monthly_fee): array
    {
        return $this->makeItem([
            'type' => InvoiceItemType::CHARGE,
            'source' => InvoiceItemSource::SYSTEM,
            'name' => "Opłata stała (\"Czesne\" {$child->zone->name}) za miesiąc {$this->calendarService->getMonthName($billingDate)} {$billingDate->year}",
            'quantity' => 1,
            'unit_price' => $monthly_fee,
        ]);
    }

    private function generateMealItem(Child $child, Carbon $billingDate, int $holidayDaysCount): array
    {
        $cateringQuantity = max(0, $this->calendarService->getWorkdaysCount($billingDate) - $holidayDaysCount);

        return $this->makeItem([
            'type' => InvoiceItemType::CHARGE,
            'source' => InvoiceItemSource::SYSTEM,
            'name' => "Wyżywienie dziecka {$child->first_name} za miesiąc {$this->calendarService->getMonthName($billingDate)} {$billingDate->year}",
            'quantity' => $cateringQuantity,
            'unit_price' => self::LUNCH_COST,
        ]);
    }

    private function generateAdvanceMealItem(Child $child, Carbon $billingDate): ?array
    {
        $advanceBillingDate = $billingDate->copy()->subMonth();

        $advanceCateringQuantity = $child->absences()
            ->whereMonth('absent_at', $advanceBillingDate->month)
            ->whereYear('absent_at', $advanceBillingDate->year)
            ->where('charge_catering', false)
            ->count();

        if ($advanceCateringQuantity <= 0) {
            return null;
        }

        return $this->makeItem([
            'type' => InvoiceItemType::ADVANCE,
            'source' => InvoiceItemSource::SYSTEM,
            'name' => "Otrzymana zaliczka (wyżywienie miesiąc {$this->calendarService->getMonthName($advanceBillingDate)} $advanceBillingDate->year)",
            'quantity' => $advanceCateringQuantity,
            'unit_price' => -abs(self::ADVANCE_LUNCH_COST),
        ]);
    }

    /**
     * @param  InvoiceItemData[]  $items
     */
    private function generateCustomItems(array $items): array
    {

        return Arr::map($items, fn (InvoiceItemData $item) => $this->makeItem([
            'type' => InvoiceItemType::CHARGE,
            'source' => InvoiceItemSource::MANUAL,
            'name' => $item->name,
            'quantity' => $item->quantity,
            'unit_price' => $item->price,
        ]));

    }

    /**
     * @param  InvoiceItemData[]  $discounts
     */
    private function generateDiscountItems(array $discounts): array
    {
        return Arr::map($discounts, fn (InvoiceItemData $discount) => $this->makeItem([
            'type' => InvoiceItemType::DISCOUNT,
            'source' => InvoiceItemSource::MANUAL,
            'name' => $discount->name,
            'quantity' => $discount->quantity,
            'unit_price' => -abs($discount->price),
        ]));
    }
}
