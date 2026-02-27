<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\PromotionTransaction;

class SyncPromotions extends Command
{
    protected $signature = 'app:sync-promotions';
    protected $description = 'Sync promotions data from Darwinbox API';

    public function handle()
    {
        $url = 'https://kpncorporation.darwinbox.com/reportsbuilderapi/reportdatav2';

        $headers = [
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh',
            'Content-Type'  => 'application/json',
            'Cookie'        => '_cfuvid=kY2vqtS1haaFahMTfH3vDu2tpCwvWDEPVQHYX1x.jSc-1756348863016-0.0.1.1-604800000; session=bc109f14650cef28611294a44b73b642',
        ];

        $body = [
            'api_key'   => '9e6279e6cbb4a39315b3c94f07c134c0f949202a0c14a046b6595ae2398a498c9fa4ca74c58bb0bcad3218e574d273a9029536bd867d4574d3135148fbd3691f',
            'report_id' => '32af7506857213', // promotion report_id
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $body);

            if (!$response->successful()) {
                $this->error("Gagal ambil data promotions. Status: " . $response->status());
                $this->error($response->body());
                return;
            }

            $data = $response->json();
            $rows = $data['response']['data'] ?? [];

            if (empty($rows)) {
                $this->warn("Tidak ada data promotion dari API.");
                return;
            }

            $this->info("Jumlah data promotion dari API: " . count($rows));

            $apiKeys = [];

            foreach ($rows as $row) {
                $employeeId = $row['Employee Id'] ?? null;
                $from   = $this->parseDate($row['From'] ?? null); // API: "Form" → DB: "from"

                if (!$employeeId || !$from) {
                    continue;
                }

                $apiKeys[] = $employeeId . '|' . $from;

                PromotionTransaction::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'from'        => $from,
                    ],
                    [
                        'to'            => $this->parseDate($item['To'] ?? null),
                        'employee_name' => $row['Employee Name'] ?? null,
                        'is_promotion'  => $row['Is Promotion'] ?? null,
                        'job_level'     => $row['Job Level'] ?? null,
                        'job_level_code'=> $row['Job Level Code'] ?? null,
                    ]
                );
            }

            // Soft delete data yang ada di DB tapi tidak ada di API
            PromotionTransaction::whereNotIn(
                DB::raw("CONCAT(employee_id,'|',`from`)"),
                $apiKeys
            )->delete();

            $this->info("Sync promotions selesai. Data berhasil disimpan & diperbarui.");

        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
        }
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        $parsed = \DateTime::createFromFormat('d-m-Y', $date);
        return $parsed ? $parsed->format('Y-m-d') : null;
    }
}
