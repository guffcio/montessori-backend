<?php

namespace App\Services;

use Carbon\Carbon;

class CalendarService
{
    public function getWorkdaysCount(Carbon $date): int
    {
        $startOfMonth = Carbon::createFromDate($date->year, $date->month)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($date->year, $date->month)->endOfMonth();

        $workingDays = $startOfMonth->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday();
        }, $endOfMonth->addDay());

        return $workingDays;
    }

    public function getMonthName(Carbon $date): string
    {
        return ucfirst($date->translatedFormat('F'));
    }
}
