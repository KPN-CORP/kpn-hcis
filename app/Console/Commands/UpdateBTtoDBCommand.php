<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateBTtoDBJob;
use Illuminate\Support\Facades\Log;

class UpdateBTtoDBCommand extends Command
{
    protected $signature = 'update:bt-to-db';
    protected $description = 'Update Business Trip data to Database';

    public function handle()
    {
        Log::info('UpdateBTtoDBCommand started');
        UpdateBTtoDBJob::dispatch();
        Log::info('UpdateBTtoDBJob dispatched');

        $this->info('Job dispatched successfully!');
    }
}
