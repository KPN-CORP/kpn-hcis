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

        if ($start->gte($end)) {
            return 0;
        }

        $holidays = master_holiday::pluck("tanggal_libur")->toArray();

        $days = 0;

        while ($start->lt($end)) {
            $start->addDay();

            if (in_array($start->format("Y-m-d"), $holidays)) {
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
