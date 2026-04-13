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

    public static function different_days_exclude_holiday(
        $start_date,
        $end_date,
    ) {
        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        if ($start->gte($end)) {
            return 0;
        }

        $holidays = master_holiday::pluck("tanggal_libur")->toArray();

        $days = 0;

        while ($start->lt($end)) {
            $start->addDay();

            if (in_array($start->format("Y-m-d"), $holidays)) {
                $days--;
                continue;
            }

            $days++;
        }

        if ($days > 0) {
            $days--;
        }

        return $days;
    }
}
