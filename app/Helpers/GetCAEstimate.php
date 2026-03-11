<?php

namespace App\Helpers;

use Carbon\Carbon;

use App\Models\master_holiday;

class GetCAEstimate
{
    public const TOTAL_DAY_POLICY = 3;

    public static function declare_estimate_include_holiday(
        $end_date,
        $total_day_policy = null,
    ) {
        if (is_null($total_day_policy)) {
            $total_day_policy = self::TOTAL_DAY_POLICY;
        }

        if ($total_day_policy <= 0) {
            return Carbon::parse($end_date);
        }

        $date = Carbon::parse($end_date);

        $date->addDays($total_day_policy);

        return $date;
    }

    public static function declare_estimate_exclude_holiday(
        $end_date,
        $total_day_policy = null,
    ) {
        if (is_null($total_day_policy)) {
            $total_day_policy = self::TOTAL_DAY_POLICY;
        }

        if ($total_day_policy <= 0) {
            return Carbon::parse($end_date);
        }

        $start = Carbon::parse($end_date);
        $current = $start->copy();

        $daysAdded = 0;

        $holidays = master_holiday::pluck("tanggal_libur")->toArray();

        while ($daysAdded < $total_day_policy) {
            $current->addDay();

            if (in_array($current->format("Y-m-d"), $holidays)) {
                continue;
            }

            $daysAdded++;
        }

        return $current;
    }
}
