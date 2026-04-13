<?php

namespace App\Helpers;

use Carbon\Carbon;

use App\Models\master_holiday;

class CalculateDays
{
    public static function different_days_include_holiday(
        $start_date,
        $end_date,
    ) {
        $days = (int) Carbon::parse($start_date)->diffInDays($end_date, false);

        if ($days < 0) {
            $days--;
        }

        return $days;
    }

    public static function different_days_exclude_holiday($start_date, $end_date)
    {
        $start = Carbon::parse($start_date)->startOfDay();
        $end = Carbon::parse($end_date)->startOfDay();

        $holidays = master_holiday::pluck("tanggal_libur")
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $step = $start->lt($end) ? 1 : -1;

        $days = 0;

        while (!$start->eq($end)) {
            $start->addDay($step);

            if (in_array($start->format('Y-m-d'), $holidays)) {
                continue;
            }

            $days += $step;
        }

        return $days;
    }
}
