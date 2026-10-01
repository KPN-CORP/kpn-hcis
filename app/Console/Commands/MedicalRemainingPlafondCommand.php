<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\MedicalRemainingPlafondJob;
use Illuminate\Support\Facades\Log;

class MedicalRemainingPlafondCommand extends Command
{
    protected $signature = 'medical:remaining-plafond';
    protected $description = 'Medical Remaining Plafond';

    public function handle()
    {
        Log::info('MedicalRemainingPlafondCommand started');

        MedicalRemainingPlafondJob::dispatch()->onQueue('kpn-hcis');

        Log::info('MedicalRemainingPlafondCommand dispatched');

        $this->info('MedicalRemainingPlafondCommand Job dispatched successfully!');
    }
}
