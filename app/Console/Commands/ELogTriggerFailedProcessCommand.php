<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class ELogTriggerFailedProcessCommand extends Command
{
    protected $signature = "elog:trigger-failed-process";
    protected $description = "ELog Trigger Failed Process";

    public function handle()
    {
        Log::info("⏰ [Command] Running ELogTriggerFailedProcessCommand at " . now());

        //

        $this->info("[Command] Running ELogTriggerFailedProcessCommand successfully.");
    }
}
