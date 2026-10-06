<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class CADeclarationReminder extends Command
{
    protected $signature = "ca:declaration-reminder";
    protected $description = "CA Declaration Reminder";

    public function handle()
    {
        $today = Carbon::now();

        Log::info("⏰ [Command] Running CADeclarationReminder at " . $today);

        // ...

        $this->info("✔ [Command] CADeclarationReminder DONE at " . Carbon::now());
    }
}
