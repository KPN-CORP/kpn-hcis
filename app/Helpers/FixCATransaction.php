<?php

namespace App\Helpers;

use App\Models\CATransaction as CATransactionModel;
use App\Helpers\GetCAEstimate as GetCAEstimateHelper;

class FixCATransaction
{
    public static function declare_estimate_include_holiday(
        $id,
        $end_date,
        $total_day_policy,
    ) {
        $ca_transaction = CATransactionModel::where("id", $id)->first();

        if (!$ca_transaction) {
            return;
        }

        $declare_estimate_date = GetCAEstimateHelper::declare_estimate_include_holiday(
            $end_date,
            $total_day_policy,
        );

        $ca_transaction->declare_estimate = $declare_estimate_date;

        $ca_transaction->save();
    }

    public static function declare_estimate_exclude_holiday(
        $id,
        $end_date,
        $total_day_policy,
    ) {
        $ca_transaction = CATransactionModel::where("id", $id)->first();

        if (!$ca_transaction) {
            return;
        }

        $declare_estimate_date = GetCAEstimateHelper::declare_estimate_exclude_holiday(
            $end_date,
            $total_day_policy,
        );

        $ca_transaction->declare_estimate = $declare_estimate_date;

        $ca_transaction->save();
    }
}
