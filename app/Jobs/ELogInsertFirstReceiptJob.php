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

use App\Models\HealthCoverage as HealthCoverageModel;
use App\Services\ELogService;

class ELogInsertFirstReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(public int $coverageId)
    {
        $this->onQueue('kpn-hcis');
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ELogService $eLogService): void
    {
        $coverage = HealthCoverageModel::find($this->coverageId);

        if (!$coverage) {
            Log::warning('ELog: coverage tidak ditemukan', ['id' => $this->coverageId]);
            return;
        }

        $result = $eLogService->insertFirstReceipt($coverage);

        if (!($result['status'] ?? false)) {
            Log::error('ELog insertFirstReceipt gagal', [
                'coverage_id' => $this->coverageId,
                'error'       => $result['error'] ?? null,
            ]);

            throw new RuntimeException('ELog insertFirstReceipt gagal untuk coverage ' . $this->coverageId);
        }

        Log::info('ELog insertFirstReceipt sukses', ['coverage_id' => $this->coverageId]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('ELogInsertFirstReceiptJob failed', [
            'coverage_id' => $this->coverageId,
            'error'       => $e->getMessage(),
        ]);
    }
}
