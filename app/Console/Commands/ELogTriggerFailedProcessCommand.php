<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

use App\Jobs\ELogInsertFirstReceiptJob;
use App\Models\HealthCoverage as HealthCoverageModel;

class ELogTriggerFailedProcessCommand extends Command
{
    protected $signature = "elog:trigger-failed-process";
    protected $description = "ELog Trigger Failed Process";

    public function handle()
    {
        $today = Carbon::now();

        Log::info("⏰ [Command] Running ELogTriggerFailedProcessCommand at " . now());

        $coverages = HealthCoverageModel::whereNull("elog_insert_first_receipt_sync_date")
            ->where("status", "Done")
            ->where("period", ">=", "2026")
            // TODO: add filter where date greater than today (current release date)
            ->whereNull("deleted_at")
            ->get();

        foreach($coverages as $coverage) {
            if (!$coverage) {
                continue;
            }

            ELogInsertFirstReceiptJob::dispatch($coverage->id);
        }

        $this->info("[Command] Running ELogTriggerFailedProcessCommand successfully.");
    }
}
