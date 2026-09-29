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

use App\Services\ELogService;

class ELogInsertFirstReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(public string $id)
    {
        $this->onQueue('kpn-hcis');
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ELogService $eLogService): void
    {
        $result = $eLogService->insertFirstReceipt($this->id);
        if (!($result['status'] ?? false)) {
            Log::error('ELogInsertFirstReceipt gagal', [
                'coverage_id' => $this->id,
                'error'       => $result['error'] ?? null,
            ]);

            throw new RuntimeException('ELogInsertFirstReceipt gagal untuk coverage ' . $this->id);
        }

        Log::info('ELogInsertFirstReceipt sukses', ['coverage' => $this->id]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('ELogInsertFirstReceiptJob failed', [
            'coverage_id' => $this->id,
            'error'       => $e->getMessage(),
        ]);
    }
}
