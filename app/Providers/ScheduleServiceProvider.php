<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FetchAndStoreEmployees;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule)
    {
        $schedule->command('fetch:employees')->dailyAt('00:03');
        $schedule->command('fetch:employees2')->dailyAt('00:07');
        // $schedule->command('update:employee-access-menu')->dailyAt('00:01');
        // $schedule->command('app:reminderSchedules')->dailyAt('08:00');
        // $schedule->command('app:inactive-employees')->dailyAt('00:20');
        // $schedule->command('app:update-designations')->dailyAt('01:00');
        $schedule->command('update:bt-to-db')->dailyAt('00:15')->withoutOverlapping();
        $schedule->command('attendance:update-bt')->dailyAt('02:00');
    }
}