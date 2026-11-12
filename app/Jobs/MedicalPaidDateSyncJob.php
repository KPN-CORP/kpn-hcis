<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;
use RuntimeException;

use App\Services\ODataService;

class MedicalPaidDateSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(public string $id)
    {
        $this->onQueue(config('queue.hcis_queue'));
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ODataService $service): void
    {
        $result = $service->syncMedicalPaidDate($this->id);
        if (!($result['status'] ?? false)) {
            Log::error('MedicalPaidDateSyncJob gagal', [
                'coverage_id' => $this->id,
                'error'       => $result['error'] ?? null,
                'result'      => $result,
            ]);

            throw new RuntimeException('❌ MedicalPaidDateSyncJob gagal untuk coverage ' . $this->id);
        }

        Log::info('✔️ MedicalPaidDateSyncJob sukses', ['coverage' => $this->id]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('MedicalPaidDateSyncJob failed', [
            'coverage_id' => $this->id,
            'error'       => $e->getMessage(),
        ]);
    }
}
