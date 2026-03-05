<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Http\Controllers\AttendanceController;

class UpdateBTAttendance extends Command
{
    protected $signature = "attendance:update-bt";

    protected $description = "Update Business Travel Attendance to DB";

    public function handle()
    {
        Log::info("⏰ [Command] Running UpdateBTAttendance at " . now());
        // Panggil method dari AttendanceController
        $controller = new AttendanceController();
        $controller->UpdateBTtoDBnextstep();

        $this->info("Business Travel Attendance updated successfully.");
    }
}
