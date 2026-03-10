<?php

namespace App\Helpers;

use Carbon\Carbon;

use App\Models\master_holiday;

class GetCAEstimate
{
    public static function declare_estimate_include_holiday(
        $end_date,
        $total_day_policy,
    ) {
        if ($total_day_policy <= 0) {
            return Carbon::parse($end_date)->toDateString();
        }

        $date = Carbon::parse($end_date);

        $date->addDays($total_day_policy);

        return $date->toDateString();
    }

    public static function declare_estimate_exclude_holiday(
        $end_date,
        $total_day_policy,
    ) {
        if ($total_day_policy <= 0) {
            return Carbon::parse($end_date)->toDateString();
        }

        $start = Carbon::parse($end_date);
        $current = $start->copy();

        $daysAdded = 0;

        $holidays = master_holiday::pluck("tanggal_libur")->toArray();

        while ($daysAdded < $total_day_policy) {
            $current->addDay();

            if (
                !$current->isWeekend() &&
                !in_array($current->toDateString(), $holidays)
            ) {
                $daysAdded++;
            }
        }

        return $current->toDateString();
    }
}
