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
        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        if ($start->gt($end)) {
            return 0;
        }

        return $start->diffInDays($end) + 1;
    }

    public static function different_days_exclude_holiday(
        $start_date,
        $end_date,
    ) {
        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        if ($start->gt($end)) {
            return 0;
        }

        $holidays = master_holiday::pluck("tanggal_libur")->toArray();

        $days = 0;

        while ($start->lte($end)) {
            if (
                !$start->isWeekend() &&
                !in_array($start->toDateString(), $holidays)
            ) {
                $days++;
            }

            $start->addDay();
        }

        return $days;
    }
}
