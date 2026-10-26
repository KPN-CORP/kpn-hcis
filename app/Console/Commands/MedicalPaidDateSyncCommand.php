<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

use App\Jobs\MedicalPaidDateSyncJob;
use App\Models\HealthCoverage as HealthCoverageModel;

class MedicalPaidDateSyncCommand extends Command
{
    protected $signature = "medical:paid-date-sync";
    protected $description = "Medical Paid Date Sync";

    public function handle()
    {
        $today = Carbon::now();

        Log::info("⏰ [Command] Running MedicalPaidDateSyncCommand at " . $today);

        $coverages = HealthCoverageModel::whereNull("paid_date")
            ->where("status", "Done")
            ->where("period", ">=", "2026")
            ->whereNull("deleted_at")
            ->get();

        foreach($coverages as $coverage) {
            if (!$coverage) {
                continue;
            }

            MedicalPaidDateSyncJob::dispatch($coverage->id);
        }

        Log::info("✔️ [Command] Running MedicalPaidDateSyncCommand successfully at " . Carbon::now());
        $this->info("✔️ [Command] Running MedicalPaidDateSyncCommand successfully at " . Carbon::now());
    }
}
