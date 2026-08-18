<?php

namespace App\Services;

use Carbon\Carbon;

class CalendarService
{
    public function getWorkdaysCount(Carbon $date): int
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        return $startOfMonth->diffInDaysFiltered(
            fn (Carbon $date) => $date->isWeekday(),
            $endOfMonth
        );

    }

    public function getMonthName(Carbon $date): string
    {
        return ucfirst($date->translatedFormat('F'));
    }
}
